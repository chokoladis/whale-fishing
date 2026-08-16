<?php

declare(strict_types=1);

namespace App\DTO\Http\Response\Coin;

readonly class CoinStatisticsResponse
{
    public function __construct(
        public float  $marketCap,
        public string $liquidity,
        public float  $totalSupply,
        public string $circulationSupply,
        public ?string $volume = null,
        public ?string $volume24 = null,
        public ?int   $maxSupply = null,
        //"ath":1.32,"atl":0.572521,
    )
    {
    }
}
