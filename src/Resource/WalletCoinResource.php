<?php

namespace App\Resource;

use App\Entity\WalletCoin;
use App\Helper\StrHelper;
use App\Resource\Coin\CoinResource;

class WalletCoinResource
{
    public function __construct(
        private CoinResource $coinResource,
    )
    {
    }

    /**
     * @param WalletCoin $walletCoin
     * @return array<string, mixed>
     */
    public function get(WalletCoin $walletCoin): array
    {
        $data = [
            'coin' => $this->coinResource->itemWithPrice($walletCoin->getCoin()),
            'balance' => StrHelper::trimZeros($walletCoin->getBalance()),
            'avgPrice' => StrHelper::trimZeros($walletCoin->getAvgPrice())
        ];

        $price = $walletCoin->getCoin()->getAvgPrice();

        if ($price && $price != 0.0) {
            $data['total'] = $walletCoin->getTotalValue($price);
            $data['pnl'] = $walletCoin->getPnl($price);
        }

        return $data;
    }
}
