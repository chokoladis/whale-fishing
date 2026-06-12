<?php

declare(strict_types=1);

namespace App\DTO;

readonly class CoinDTO
{
    public function __construct(
        public string $network,
        public string $contractAddress,
        public string $symbol,
        public int $decimal,
    )
    {
    }
}
