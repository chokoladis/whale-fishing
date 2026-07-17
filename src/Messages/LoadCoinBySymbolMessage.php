<?php

namespace App\Messages;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class LoadCoinBySymbolMessage
{
    public function __construct(
        public string $symbol,
    )
    {
    }
}
