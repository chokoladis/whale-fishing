<?php

namespace App\Messages;

readonly class UpdateCoinPrice
{
    public function __construct(
        public string $contractAddress,
    )
    {
    }
}
