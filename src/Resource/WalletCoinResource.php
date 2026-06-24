<?php

namespace App\Resource;

use App\Entity\WalletCoin;

class WalletCoinResource
{
    public function __construct(
        private CoinResource $coinResource,
    ){}

    /**
     * @param WalletCoin $walletCoin
     * @return array<string, mixed>
     */
    public function get(WalletCoin $walletCoin) : array
    {
        $data = [
            'coin' => $this->coinResource->itemWithPrice($walletCoin->getCoin()),
            'balance' => rtrim(rtrim($walletCoin->getBalance(), '0'),'.'),
            'avgPrice' => rtrim(rtrim($walletCoin->getAvgPrice(),'0'),'.'), //todo
        ];

        $price = $walletCoin->getCoin()->getPrice();

        if ($price && $price != 0.0) {
            $data['total'] = $walletCoin->getTotalValue($price);
            $data['pnl'] = $walletCoin->getPnl($price);
        }

        return $data;
    }
}
