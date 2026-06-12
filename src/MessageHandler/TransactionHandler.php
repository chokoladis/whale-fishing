<?php

namespace App\MessageHandler;

use App\DTO\Http\Response\TransactionDTO;
use App\Service\Coin\CoinService;
use App\Service\Coin\WalletService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TransactionHandler
{
    const CHECKER_RANGE_FROM = 50000;

    function __construct(
        private CoinService $coinService,
        private WalletService $walletService,
    )
    {
    }

    public function __invoke(TransactionDTO $transaction) : void
    {
        // получаем транзакцию из alchemy -> создаем монету -> создаем кошелек -> транзакцию
        $coin = $this->coinService->createOrFindByTransaction($transaction);

        if (!$coin) return;

        $amount = bcdiv(
            $transaction->amountRaw,
            bcpow('10', (string)$coin->getDecimal()),
            $coin->getDecimal()
        );

        if ($amount >= (string) self::CHECKER_RANGE_FROM){
            $this->walletService->addTransactions($transaction, $coin);
        }
    }
}
