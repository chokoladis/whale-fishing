<?php

namespace App\Service;

use App\Dispatcher\SendCodeDispatcher;
use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\DTO\Http\Request\Auth\PasswordRestoreSendToken;
use App\Entity\User\PasswordRestore;
use App\Entity\User\User;
use App\Enum\User\Channel;
use App\Exception\RateLimitException;
use App\Repository\User\PasswordRestoreRepository;
use App\Repository\User\UserRepository;
use App\Service\Auth\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Exception\ValidatorException;

class PasswordService
{
    public function __construct(
        private SendCodeDispatcher          $senderCodeDispatcher,
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
        if (!$request->email && !$request->phone){
            throw new BadRequestException('Укажите один из способов восстановления');
        }

        if ($request->email){
            $user = $this->userRepository->findOneBy([
                'email' => $request->email
            ]);

            if (!$user)
                throw new NotFoundResourceException("Пользователь с таким email не был найден");

            $channel = Channel::EMAIL;
        } else {
            $user = $this->userRepository->findOneBy([
                'phone' => $request->phone
            ]);

            if (!$user)
                throw new NotFoundResourceException("Пользователь с таким телефоном не был найден");

            $channel = Channel::TELEGRAM;
        }

        $this->checkQtyRequests($user);

        $token = ByteString::fromRandom()->toString();
        $hashedToken = hash('sha256', $token);
        $newPasswordRestore = new PasswordRestore();
        $newPasswordRestore->setUser($user)->setToken($hashedToken);

        $this->entityManager->beginTransaction();
        try {
            $this->passwordRestoreRepository->save($newPasswordRestore);

            //todo send to rabbitmq
            $this->senderCodeDispatcher->send($channel, $user, $token, 'сброса пароля');
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();
            $this->servicesLogger->emergency($exception);
            throw $exception;
        }
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

        if (!$request->code)
            throw new ValidatorException('Не указан токен');

        $tokenHash = hash('sha256', $request->code);

        $row = $this->passwordRestoreRepository->getActiveByToken($tokenHash);
        if (!$row)
            throw new NotFoundResourceException('Указан истекший или некорректный код');

        $user = $row->getUser();

        $connection = $this->entityManager->getConnection();
        $connection->setAutoCommit(false);
        $connection->beginTransaction();

        try {
            $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
            $user->setUpdatedAt(new \DateTimeImmutable());

            $row->setExpiredAt(new \DateTimeImmutable());

            $this->tokenService->revokeAll($user);

            $this->entityManager->flush();

            $connection->commit();
        } catch (\Throwable $exception) {
            $this->servicesLogger->error($exception->getMessage());
            $connection->rollBack();
        }
    }
}
