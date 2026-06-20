<?php

namespace App\Resource;

use App\Entity\Wallet;
use App\Entity\WalletCoin;

class WalletResource
{
    public function __construct(
        private WalletCoinResource $walletCoinResource,
    ){}

    public function detail(
        Wallet $wallet,
    )
    {
        return [
            'address' => $wallet->getAddress(),
            //todo pagination
            'coins' => array_map(fn(WalletCoin $coin) => $this->walletCoinResource->get($coin),
                $wallet->getWalletCoins()->toArray()
            ),
        ];
    }
}
