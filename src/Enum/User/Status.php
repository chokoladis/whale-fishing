<?php

namespace App\Enum\User;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED = 'blocked';
    case DELETED = 'deleted';
}
