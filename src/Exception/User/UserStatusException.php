<?php

namespace App\Exception\User;

class UserStatusException extends \Exception
{
    protected $message = 'Статус аккаунта не позволяет выполнить операцию';
}