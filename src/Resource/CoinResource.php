<?php

namespace App\Resource;

use App\Entity\Coin;
use Symfony\Component\Serializer\SerializerInterface;

class CoinResource
{

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
