<?php

namespace App\Messages;

use App\Entity\WalletCoin;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SetWalletCoinWithActualPriceMessage
{
    public function __construct(
        public int $walletCoinId,
        public \DateTimeImmutable $dataTime,
    )
    {
    }
}
