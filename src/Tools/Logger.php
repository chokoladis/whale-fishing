<?php

namespace App\Tools;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger
{
    public static ?self $logger = null;

    public static function instance(): LoggerInterface
    {
        if (static::$logger === null) {
            static::$logger = new static();
        }

        return static::$logger;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->debug($message, $context);
    }
}
