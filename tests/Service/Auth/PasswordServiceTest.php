<?php

namespace App\Tests\Service\Auth;

use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\Entity\User\PasswordRestore;
use App\Repository\User\PasswordRestoreRepository;
use App\Repository\User\UserRepository;
use App\Service\Auth\TokenService;
use App\Service\PasswordService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PasswordServiceTest extends TestCase
{
    private \App\Dispatcher\SendCodeDispatcher $dispatcher;
    private UserRepository              $userRepository;
    private PasswordRestoreRepository   $passwordRestoreRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private TokenService                $tokenService;
    private EntityManagerInterface      $entityManager;
    private PasswordService $passwordService;

    const CODE = 'OKSD_123od__eefoerinrgmdos994';
    const GOOD_PASSWORD = '123apsodWPEW))_';

    public function setUp() : void
    {
        $this->dispatcher = $this->createMock(\App\Dispatcher\SendCodeDispatcher::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordRestoreRepository = $this->createMock(PasswordRestoreRepository::class);

        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->tokenService = $this->createMock(TokenService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->passwordService = new PasswordService(
            $this->dispatcher,
            $this->userRepository,
            $this->passwordRestoreRepository,
            $this->passwordHasher,
            $this->tokenService,
            $this->entityManager,
            $this->createMock(LoggerInterface::class)
        );
    }
    public function test_passwordRestoreConfirmSuccess(): void
    {
        $this->passwordRestoreRepository->expects($this->once())
            ->method('getActiveByToken')
            ->with(self::CODE)
            ->willReturn($this->createMock(PasswordRestore::class));

        $this->passwordService->restore(
            new PasswordRestoreConfirm(self::CODE, self::GOOD_PASSWORD, self::GOOD_PASSWORD)
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_passwordRestoreConfirmErrorCode(): void
    {
        $this->expectException(\Exception::class);

        $this->passwordService->restore(
            new PasswordRestoreConfirm('size5', self::GOOD_PASSWORD, self::GOOD_PASSWORD)
        );
    }

    public function test_passwordRestoreConfirmErrorNotFound(): void
    {
        $this->passwordRestoreRepository->expects($this->once())
            ->method('getActiveByToken')
            ->with(self::CODE)
            ->willReturn(null);

        $this->expectException(NotFoundResourceException::class);

        $this->passwordService->restore(
            new PasswordRestoreConfirm(self::CODE, self::GOOD_PASSWORD, self::GOOD_PASSWORD)
        );
    }
}
