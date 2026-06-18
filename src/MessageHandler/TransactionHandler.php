<?php

namespace App\MessageHandler;

use App\DTO\Http\Response\TransactionDTO;
use App\Messages\UpdateCoinPrice;
use App\Service\Coin\CoinService;
use App\Service\Wallet\WalletService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TransactionHandler
{
    const int CHECKER_RANGE_FROM = 50000;

    function __construct(
        private CoinService $coinService,
        private WalletService $walletService,
        protected MessageBusInterface $bus,
    )
    {
    }

    public function __invoke(TransactionDTO $transaction) : void
    {
        // получаем транзакцию из alchemy -> создаем монету -> создаем кошелек -> транзакцию
        $coin = $this->coinService->createOrFindByTransaction($transaction);
        if (!$coin) return;

        $this->bus->dispatch(new UpdateCoinPrice($transaction->contractAddress));

        $amount = bcdiv(
            $transaction->amountRaw,
            bcpow('10', (string)$coin->getDecimal()),
            $coin->getDecimal()
        );

        if ($amount >= self::CHECKER_RANGE_FROM){
            $this->walletService->addTransactions($transaction, $coin);
        }
    }
}
