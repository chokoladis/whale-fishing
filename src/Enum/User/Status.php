<?php

namespace App\Enum\User;

enum Status: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case DELETED = 'deleted';
}
