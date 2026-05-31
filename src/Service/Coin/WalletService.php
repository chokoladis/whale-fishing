<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Repository\WalletRepository;

class WalletService
{
    const int ITEMS_PER_PAGE = 10;
    const float MIN_VALUE_TOP_HOLDER = 100000;

    public function __construct(
        private WalletRepository $walletRepository,
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

        $wallets = $this->walletRepository->findByMinValue($symbol, self::MIN_VALUE_TOP_HOLDER);
        dump($wallets);
        if (empty($wallets)) {
            $wallets = $this->alchemyService->getTopHolders($symbol);
        }

        return $wallets;
    }
}
