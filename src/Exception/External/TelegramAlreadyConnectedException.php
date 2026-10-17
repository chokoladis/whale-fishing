<?php

namespace App\Exception\External;

class TelegramAlreadyConnectedException extends \Exception
{
    protected $message = 'Telegram Already Connected';
}
