<?php

namespace App\MessageHandler;

use App\Messages\TransactionMessage;
use App\Messages\UpdateCoinPriceMessage;
use App\Service\Coin\CoinService;
use App\Service\Wallet\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        #[Autowire(service: 'monolog.logger.commands')]
        private LoggerInterface $logger
    )
    {
    }

    public function __invoke(TransactionMessage $transaction) : void
    {
        $data = $transaction->dto;

        // получаем транзакцию из alchemy -> создаем монету -> создаем кошелек -> транзакцию
        $coinContract = $this->coinService->createOrFindByTransaction($data);

        if (!$coinContract) return;

        $this->bus->dispatch(
            new UpdateCoinPriceMessage(
                $coinContract->getCoin()->getSymbol(),
                $data->contractAddress
            )
        );

        $this->logger->debug('after dispatch update price');
        $amount = bcdiv(
            $data->amountRaw,
            bcpow('10', (string)$coinContract->getDecimal()),
            $coinContract->getDecimal()
        );

//        todo check
        if ($amount >= self::CHECKER_RANGE_FROM){
            $this->walletService->addTransactions($data, $coinContract->getCoin());
        }
    }
}
