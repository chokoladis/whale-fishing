<?php

namespace App\Messages;

readonly class GetCoinBySymbolMessage
{
    public function __construct(
        public string $symbol,
    )
    {
    }
}
