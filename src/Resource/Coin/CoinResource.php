<?php

namespace App\Resource\Coin;

use App\Entity\Coin;
use App\Entity\CoinContract;

class CoinResource
{

    public function __construct(
        private CoinContractResource $coinContractResource
    )
    {
    }

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
            'contracts' => $coin->getCoinContract()->map(function (CoinContract $contract) { //todo
                return $this->coinContractResource->item($contract);
            }),
        ];
    }
}
