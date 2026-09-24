<?php

namespace App\Interface\Notification;

use Symfony\Component\Security\Core\User\UserInterface;

interface VerificationCodeSender
{
    public function send(UserInterface $user,  string $code) : void;
}
