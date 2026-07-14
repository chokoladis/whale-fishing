<?php

declare(strict_types=1);

namespace App\DTO\Http\Response\Coin;

use phpDocumentor\Reflection\Types\Integer;

readonly class CoinStatisticsResponse
{
    public function __construct(
        public int $marketCap,
        public string $volume,
        public string $liquidity,
        public int $totalSupply,
        public string $circulationSupply,
        //todo
        // max supply
        //"volume_change_24h":96.80744171142578,
        //"volume_7d":1158409216,
        //"ath":1.32,"atl":0.572521,
    )
    {
    }
}
