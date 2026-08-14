<?php

namespace App\DTO\Http\Response\Coin;

final readonly class BatchPricesPayloadItem
{
    public function  __construct(
        public string $address,
        public string $chainId,
        public ?float $priceUSD,
        public ?float $marketCapUSD,
//          "liquidityUSD": 123,
//          "liquidityMaxUSD": 123
    )
    {
    }
}