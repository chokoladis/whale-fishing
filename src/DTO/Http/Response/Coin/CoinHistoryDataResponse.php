<?php

namespace App\DTO\Http\Response\Coin;

readonly final class CoinHistoryDataResponse
{
    function __construct(
        public string $symbol,
        public string $price,
        public string $marketCap
    )
    {
    }
}