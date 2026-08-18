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
        //todo optimize
        $data = [
            'coin' => $this->coinResource->itemWithPrice($walletCoin->getCoin()),
            'balance' => StrHelper::trimZeros($walletCoin->getBalance()),
            'avgPrice' => StrHelper::trimZeros($walletCoin->getAvgPrice())
        ];

        $price = $walletCoin->getCoin()->getAvgPrice();
        $decimal = $walletCoin->getCoin()->getCoinContract()->current()->getDecimal();

        if ($price && $price != 0.0) {
            $data['total'] = $walletCoin->getTotalValue($price, $decimal);
            $data['pnl'] = $walletCoin->getPnl($price, $decimal);
        }

        return $data;
    }
}
