<?php

namespace App\Interface\External;

use App\DTO\Http\Response\Coin\CoinDetailResponse;

interface GetCoinDetailInterface
{
    public function getCoinDetail(string $network, string $contractAddress) : CoinDetailResponse;

}
