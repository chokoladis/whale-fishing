<?php

namespace App\Resource;

use App\Entity\Transaction;
use App\Helper\StrHelper;
use App\Resource\Coin\CoinResource;

class TransactionResource
{
    public function __construct(
        private CoinResource   $coinResource,
        private WalletResource $walletResource,
    )
    {
    }

    /**
     * @param Transaction $transaction
     * @return array<string, mixed>
     */
    public function getShortData(
        Transaction $transaction,
    ): array
    {
        return [
            'coin' => $this->coinResource->itemWithPrice($transaction->getCoin()),
            'wallet' => $this->walletResource->short($transaction->getWallet()),
            'hash' => $transaction->getHash(),
            'from' => $transaction->getFrom(),
            'to' => $transaction->getTo(),
            'amount' => StrHelper::trimZeros($transaction->getAmount()),
            'createdAt' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
