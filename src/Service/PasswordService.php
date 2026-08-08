<?php

namespace App\Service;

use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\DTO\Http\Request\Auth\PasswordRestoreSendToken;
use App\Entity\User;
use App\Exception\RateLimitException;
use App\Interface\SendTokenInterface;
use App\Repository\PasswordRestoreRepository;
use App\Repository\UserRepository;
use App\Service\Auth\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Exception\ValidatorException;

class PasswordService
{
    public function __construct(
        private SendTokenInterface          $senderToken,
        private UserRepository              $userRepository,
        private PasswordRestoreRepository   $passwordRestoreRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenService                $tokenService,
        private EntityManagerInterface      $entityManager,
        #[Autowire(env: 'APP_SECRET')]
        private string                      $secret,
        private LoggerInterface             $servicesLogger
    )
    {
    }

    public function sendToken(PasswordRestoreSendToken $request): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $request->email
        ]);

        if (!$user)
            throw new NotFoundResourceException("Пользователь с таким email не был найден");

        $this->checkQtyRequests($user);

        $this->senderToken
            ->setUser($user)
            ->sendToken();
    }

    private function checkQtyRequests(User $user): void
    {
        // todo cleaner by cron
        $collection = $this->passwordRestoreRepository->getRowsByUserIdForDay($user);
        $count = count($collection);

        if (!$count)
            return;

        $now = new \DateTimeImmutable();
        $lastRequest = current($collection);

        if ($count > 3) {
            throw new RateLimitException('Код уже был отправлен, попробуйте позже');
        } else {
            //minutes
            $diff = ($now->getTimestamp() - $lastRequest->getCreatedAt()->getTimestamp()) / 60;
            if ($count == 3 && $diff < 30) {
                throw new RateLimitException('Код уже был отправлен, попробуйте позже');
            } elseif ($count == 2 && $diff < 10) {
                throw new RateLimitException('Код уже был отправлен, попробуйте позже');
            }
        }
    }

    public function restore(PasswordRestoreConfirm $request): void
    {
        //todo rate limit на уровне мидлы или просто фреймворка/сервера

        if (!$request->token)
            throw new ValidatorException('Не указан токен');

        $tokenHash = hash('sha256', $request->token . $this->secret);

        $row = $this->passwordRestoreRepository->getActiveByToken($tokenHash);
        if (!$row)
            throw new NotFoundResourceException('Указан истекший или некорректный токен');

        $user = $row->getUser();

        $conn = $this->entityManager->getConnection();

        try {
            $conn->beginTransaction();

            $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);

            $row->setExpiredAt(new \DateTimeImmutable());
            $this->entityManager->persist($row);

            $this->tokenService->revokeAll($user);

            $this->entityManager->flush();

            $conn->commit();
        } catch (\Throwable $exception) {
            $this->servicesLogger->error($exception->getMessage());
            $conn->rollBack();
        }
    }
}
