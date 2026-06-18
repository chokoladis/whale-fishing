<?php

namespace App\Service\Coin;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Repository\CoinRepository;
use App\Resource\CoinResource;

class PriceService
{
    public function __construct(
        private CoinRepository                             $coinRepository,
        private CoinResource                               $coinResource,
        private \App\Service\External\Alchemy\PriceService $alchemyPriceService,
    )
    {
    }

    public function getPriceBySymbol(string $symbol)
    {
        $symbol = strtoupper(trim($symbol));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $coin = $this->coinRepository->findOneBy(['symbol' => $symbol]);
        if (empty($coin)) {
            $coin = $this->alchemyPriceService->getPriceBySymbol($symbol);
        }

        return $this->coinResource->itemWithPrice($coin);
    }


}
