<?php

namespace App\Interface\Notification;

use App\Enum\User\TypeVerify;
use Symfony\Component\Security\Core\User\UserInterface;

interface VerificationCodeSender
{
    public function send(UserInterface $user,  string $code) : void;
    public function support(TypeVerify $type): bool; //todo делибльная проверка, переделать
}
