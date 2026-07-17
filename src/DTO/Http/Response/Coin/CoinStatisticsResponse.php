<?php

declare(strict_types=1);

namespace App\DTO\Http\Response\Coin;

readonly class CoinStatisticsResponse
{
    public function __construct(
        public float $marketCap,
        public string $volume,
        public string $liquidity,
        public float $totalSupply,
        public string $circulationSupply,
        public ?int $maxSupply = null,
        //todo
        //"volume_change_24h":96.80744171142578,
        //"volume_7d":1158409216,
        //"ath":1.32,"atl":0.572521,
    )
    {
    }
}
