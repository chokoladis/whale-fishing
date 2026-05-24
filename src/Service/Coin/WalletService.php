<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Repository\CoinRepository;
use App\Resource\CoinResource;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class WalletService
{
    const ITEMS_PER_PAGE = 10;

    public function __construct(
//        private CoinRepository $coinRepository,
//        private CoinResource $coinResource,
        private \App\Service\Alchemy\WalletService $alchemyService,
    )
    {
    }

    public function getTopHolders(string $coinName)
    {
        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $coin = $this->coinRepository->findOneBy(['symbol' => $symbol]);
        if (empty($coin)) {

        }
        $httpRequest = HttpClient::create();

        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }
    }
}
