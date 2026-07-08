<?php

namespace App\Tool\Notification;

use App\Entity\PasswordRestore;
use App\Entity\User;
use App\Interface\SendTokenInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class BaseSenderToken implements SendTokenInterface
{

    protected User $user;
    protected string $direction;
    protected string $token;

    public function __construct(
        public EntityManagerInterface $entityManager,
    )
    {
    }

    public function setUser(User $user) : self
    {
        $this->user = $user;
        return $this;
    }

    protected function generateToken() : self
    {
        $this->token = bin2hex(random_bytes(32));
        return $this;
    }

    protected function getSubject() : string
    {
        return 'Сброс пароля';
    }

    protected function getHtmlMessage() : string
    {
        return sprintf('<b>Для сброса пароля вам потребуется ввести следующий код</b>
            <br/><h3>%s</h3><br/><br/>
            <i>Код действует в течении 30минут</i>', $this->token);
    }

    public function save() : void
    {
        $newPasswordRestore = new PasswordRestore;
        $newPasswordRestore->setUserId($this->user->getId());
        $newPasswordRestore->setToken($this->token);

        $this->entityManager->persist($newPasswordRestore);
        $this->entityManager->flush();
    }
}
