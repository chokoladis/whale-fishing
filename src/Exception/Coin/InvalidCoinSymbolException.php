<?php

namespace App\Exception\Coin;

class InvalidCoinSymbolException extends \Exception
{
    protected $message = 'Invalid coin symbol.';
}
