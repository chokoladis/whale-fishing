<?php

namespace App\Resource;

use App\Entity\Coin;
use Symfony\Component\Serializer\SerializerInterface;

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
            'price' => $coin->getPrice(),
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
            'price' => $coin->getPrice(),
            'links' => $coin->getLinks()->toArray(),
            'contractAddress' => $coin->getContractAddress(),
            'network' => $coin->getNetwork(),
        ];
    }
}
