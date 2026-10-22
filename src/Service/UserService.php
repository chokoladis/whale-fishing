<?php

declare(strict_types=1);

namespace App\Service;

use App\Dispatcher\VerificationCodeDispatcher;
use App\DTO\Http\Request\Auth\RegisterRequest;
use App\DTO\Http\Request\VerificationSendRequest;
use App\DTO\Http\Response\Auth\RegisterDTO;
use App\Entity\User\User;
use App\Entity\User\UserVerification;
use App\Enum\User\Status;
use App\Exception\RateLimitException;
use App\Exception\User\UserStatusException;
use App\Repository\User\UserRepository;
use App\Repository\User\UserVerificationRepository;
use App\Resource\ProfileResource;
use App\Service\Auth\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public function __construct(
        private UserRepository           $userRepository,
        private ProfileResource          $profileResource,
        private JWTTokenManagerInterface $JWTTokenManager,
        private TokenService             $tokenService,
        private UserVerificationRepository $userVerificationRepository,
        private EntityManagerInterface           $manager,
        private VerificationCodeDispatcher $verificationCodeDispatcher,
        private LoggerInterface $logger
    )
    {
    }

    #[RateLimit('login_register')]
    public function register(RegisterRequest $registerRequest, Request $request): RegisterDTO
    {
        $user = $this->userRepository->add($registerRequest);

        return $this->getTokenDataByUser($request, $user);
    }

    public function delete(UserInterface $userInterface) : void
    {
        $user = $this->userRepository->getByEmail($userInterface->getUserIdentifier());
        if ($user->getStatus() === Status::DELETED) {
            throw new UserStatusException('Ваш аккаунт уже удален');
        } else if ($user->getStatus() === Status::BLOCKED) {
            throw new UserStatusException('Ваш аккаунт заблокирован');
        }

        $user->setStatus(Status::DELETED)
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user);
    }

    public function restore(UserInterface $userInterface) : void
    {
        $user = $this->userRepository->getByEmail($userInterface->getUserIdentifier());
        if ($user->getStatus() === Status::BLOCKED) // todo middleware
            throw new UserStatusException('Ваш аккаунт заблокирован');

        $user->setStatus(Status::ACTIVE)
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user);
    }

    /**
     * @param VerificationSendRequest $request
     * @param UserInterface $userInterface
     * @return void
     * @throws RateLimitException
     * @throws \Doctrine\DBAL\Exception
     * @throws UnsupportedException|RateLimitException|NotFoundResourceException
     */
    public function verify(VerificationSendRequest $request, UserInterface $userInterface)
    {
        $user = $this->userRepository->getByPhone($userInterface->getUserIdentifier());
        if (!$user) // todo middleware
            throw new NotFoundResourceException("Пользователь с таким email не был найден");

        // todo sql блокировку ?
        $rows = $this->userVerificationRepository->getRowsByUserForDay($user);

        if ($count = count($rows)) {
            $now = new \DateTimeImmutable();
            $lastRequest = current($rows);

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

        $code = Uuid::v7()->toString();

        $userVerification = new UserVerification();
        $userVerification->setUser($user)->setCode($code)->setType($request->typeVerify);

        $connection = $this->manager->getConnection();
        $connection->setAutoCommit(false);
        $connection->beginTransaction();
        try {
            $this->userVerificationRepository->save($userVerification, $this->manager);
            $this->verificationCodeDispatcher->send($request->typeVerify, $user, $code);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();
    }

    public function getTokenDataByUser(Request $request, User $user): RegisterDTO
    {
        return new RegisterDTO(
            $this->profileResource->profile($user),
            $this->JWTTokenManager->create($user),
            $this->tokenService->createRefreshToken($request, $user)
        );
    }
}
