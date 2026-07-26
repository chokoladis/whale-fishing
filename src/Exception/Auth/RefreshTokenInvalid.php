<?php

namespace App\Exception\Auth;


class RefreshTokenInvalid extends \Exception
{
    protected $message = 'Refresh token invalid';
}
