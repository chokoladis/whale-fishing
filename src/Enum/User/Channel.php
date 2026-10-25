<?php

namespace App\Enum\User;

enum Channel : string
{
    case PHONE = 'phone';
    case EMAIL = 'email';
    case TELEGRAM = 'telegram';
}
