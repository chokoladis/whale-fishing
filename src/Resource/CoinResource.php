<?php

namespace App\Resource;

use App\Entity\Coin;
use Symfony\Component\Serializer\SerializerInterface;

class CoinResource
{
    function __construct(
        private SerializerInterface $serializer,
    )
    {
    }

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
}
