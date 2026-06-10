<?php

namespace App\MessageHandler;

use App\DTO\Http\Response\TransactionDTO;
use App\Service\Alchemy\TransactionService;
use App\Service\Coin\CoinService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TransactionHandler
{
    function __construct(
        private CoinService $coinService,
//        private TransactionService $transactionService,
    )
    {
    }

    public function __invoke(TransactionDTO $transaction)
    {
        $coin = $this->coinService->createOrFindByTransaction($transaction);
//        $this->transactionService->geCoinInfoByTransaction($transaction); // вызов api alchemy
    }
}
