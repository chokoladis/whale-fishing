<?php

namespace App\Resource;

use App\Entity\Transaction;

class TransactionResource
{
    public function __construct(
        private CoinResource $coinResource,
    ){}

    public function getShortData(
        Transaction $transaction,
    )
    {
        return [
            'id' => $transaction->getId(),
            'coin' => $this->coinResource->itemWithPrice($transaction->getCoin()),
            'hash' => $transaction->getHash(),
            'from' => $transaction->getFrom(),
            'to' => $transaction->getTo(),
            'amount' => $transaction->getAmount(),
            'createdAt' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
