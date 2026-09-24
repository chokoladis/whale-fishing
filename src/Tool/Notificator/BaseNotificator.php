<?php

namespace App\Tool\Notificator;

use App\Entity\User\PasswordRestore;
use App\Entity\User\User;
use App\Interface\Notification\PasswordRestoreSender;
use Doctrine\ORM\EntityManagerInterface;

abstract class BaseNotificator implements PasswordRestoreSender
{

    protected User $user;
    protected string $direction;
    protected string $token;

    public function __construct(
        public EntityManagerInterface $entityManager,
        protected string              $secret
    )
    {
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    protected function generateToken(): self
    {
        $this->token = bin2hex(random_bytes(32));
        return $this;
    }

    // todo passwordRestoreSave
    public function save(): void
    {
        $newPasswordRestore = new PasswordRestore;
        $newPasswordRestore->setUser($this->user);
        $newPasswordRestore->setToken(hash('sha256', $this->token . $this->secret));

        $this->entityManager->persist($newPasswordRestore);
        $this->entityManager->flush();
    }
}
