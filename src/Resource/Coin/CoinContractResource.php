<?php

namespace App\Resource\Coin;

use App\Entity\CoinContract;

class CoinContractResource
{

    /**
     * @param CoinContract $coin
     * @return array<string, string|int|null>
     */
    public function item(
        CoinContract $coin,
    ) : array
    {
        return [
            'network' => $coin->getNetwork(),
            'contractAddress' => $coin->getContractAddress(),
            'decimal' => $coin->getDecimal(),
            'localPrice' => $coin->getLocalPrice(),
        ];
    }
}
