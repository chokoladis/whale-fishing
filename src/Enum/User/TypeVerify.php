<?php

namespace App\Enum\User;

use App\Interface\Notification\VerificationCodeSender;
use App\Tool\Notificator\EmailNotificator;

enum TypeVerify : string
{
    case PHONE = 'phone';
    case EMAIL = 'email';
    case TELEGRAM = 'telegram';

//    public function getClassHandler()
//    {
//        return match ($this) {
//            self::EMAIL => EmailNotificator::class,
//            self::TELEGRAM => TelegramNotificator::class
//        };
//    }
//
//    public function instanceHandler() : VerificationCodeSender
//    {
//        return new ($this->getClassHandler());
//    }
}
