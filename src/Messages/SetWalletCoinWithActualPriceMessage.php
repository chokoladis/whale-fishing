<?php

namespace App\Messages;

use App\Enum\Coin\TransactionType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SetWalletCoinWithActualPriceMessage
{
    public function __construct(
        public int $walletCoinId,
        public \DateTimeImmutable $dataTime,
        public TransactionType $type,
        public string $amount,
    )
    {
    }
}
