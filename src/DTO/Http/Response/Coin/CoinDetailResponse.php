<?php

declare(strict_types=1);

namespace App\DTO\Http\Response\Coin;

readonly class CoinDetailResponse
{
    public function __construct(
        public string $name,
        public string $symbol,
        public int $decimals,
        public string $price,
        public CoinStatisticsResponse $statistics
    )
    {
    }
}
