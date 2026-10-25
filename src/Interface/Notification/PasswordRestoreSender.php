<?php

namespace App\Interface\Notification;

use App\Entity\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

interface PasswordRestoreSender
{
    public function send(UserInterface $user, string $token): void;
}
