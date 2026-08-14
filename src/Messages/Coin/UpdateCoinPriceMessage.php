<?php

namespace App\Messages\Coin;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class UpdateCoinPriceMessage
{
    public function __construct(
        public string $network,
        public string $contractAddress,
    )
    {
    }
}
