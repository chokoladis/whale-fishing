<?php

namespace App\Exception;

class RateLimitException extends \Exception
{
    protected $message = 'Превышен лимит запросов. Попробуйте позже.';
}
