<?php

declare(strict_types=1);

namespace App\DTO\Coin;

readonly class CoinShortDTO
{
    public function __construct(
        public string $symbol,
        public string $contractAddress,
        public string $network,
        public int $decimal,
    )
    {
    }
}
