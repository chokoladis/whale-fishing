<?php

namespace App\Messages;

readonly class UpdateCoinPriceMessage
{
    public function __construct(
        public string $symbol,
        public string $contractAddress,
    )
    {
    }
}
