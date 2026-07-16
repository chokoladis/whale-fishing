<?php

namespace App\DTO\Http\Response\Coin;

class CoinContractResponse
{
    public function __construct(
        public string $address,
        public string $network, //todo in enum?
        public int $decimals,
    )
    {
    }
}
