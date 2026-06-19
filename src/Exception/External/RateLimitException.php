<?php

namespace App\Exception\External;

class RateLimitException extends \Exception
{
    protected $message = 'Превышен лимит запросов';
}
