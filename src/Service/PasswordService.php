<?php

namespace App\Service;

use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\DTO\Http\Request\Auth\PasswordRestoreSendToken;
use App\Entity\PasswordRestore;
use App\Entity\User;
use App\Exception\RateLimitException;
use App\Interface\SendTokenInterface;
use App\Repository\PasswordRestoreRepository;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Exception\ValidatorException;

class PasswordService
{
    public function __construct(
        private SendTokenInterface $senderToken,
        private UserRepository $userRepository,
        private PasswordRestoreRepository $passwordRestoreRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ){}

    public function sendToken(PasswordRestoreSendToken $request)
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

    private function checkQtyRequests(User $user)
    {
        // todo cleaner by cron
        $collection = $this->passwordRestoreRepository->getRowsByUserIdForDay($user->getId());
        $count = count($collection);

        if (!$count)
            return;

        $now         = new \DateTimeImmutable();
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

    public function restore(PasswordRestoreConfirm $request)
    {
        //todo rate limit на уровне мидлы или просто фреймворка/сервера

        if (!$request->token)
            throw new ValidatorException('Не указан токен');

        $user = $this->userRepository->findOneBy([
            'email' => $request->email
        ]);

        if (!$user)
            throw new NotFoundResourceException("Пользователь с таким email не был найден");

        $row = $this->passwordRestoreRepository->getActiveByToken($request->token,$user->getId());
        if (!$row)
            throw new NotFoundResourceException('Указан истекший или некорректный токен');

        $row->setExpiredAt(new \DateTimeImmutable());

        $this->passwordRestoreRepository->save($row);

        $this->userRepository->upgradePassword(
            $user,
            $this->passwordHasher->hashPassword($user, $request->password)
        );
    }
}
