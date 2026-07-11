<?php

namespace App\Resource\Coin;

use App\Entity\Coin;

class CoinResource
{

    /**
     * @param Coin $coin
     * @return array<string, string|float|null>
     */
    public function itemWithPrice(
        Coin $coin,
    ) : array
    {
        return [
            'name' => $coin->getName(),
            'symbol' => $coin->getSymbol(),
            'price' => $coin->getAvgPrice(),
        ];
    }

    /**
     * @param Coin $coin
     * @return array<string, mixed>
     */
    public function detail(Coin $coin) : array
    {
        return [
            'name' => $coin->getName(),
            'symbol' => $coin->getSymbol(),
            'avgPrice' => $coin->getAvgPrice(),
            'links' => $coin->getLinks()->toArray(),
            'contracts' => $coin->getCoinContract()->toArray(),
        ];
    }
}
