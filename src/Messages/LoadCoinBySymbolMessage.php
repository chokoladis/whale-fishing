<?php

namespace App\Messages;

readonly class LoadCoinBySymbolMessage
{
    public function __construct(
        public string $symbol,
    )
    {
    }
}
