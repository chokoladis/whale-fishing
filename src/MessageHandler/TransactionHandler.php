<?php

namespace App\MessageHandler;

use App\Helper\StrHelper;
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
    const string CHECKER_RANGE_FROM = '50000';

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
        // update price in coin and cont contract
        $coinContract = $this->coinService->createOrFindByTransaction($data);
        if (!$coinContract) return;

        $this->bus->dispatch(
            new UpdateCoinPriceMessage(
                $data->network,
                $data->contractAddress,
            )
        );

        $amount = bcdiv(
            $data->amountRaw,
            bcpow('10', (string)$coinContract->getDecimal()),
            $coinContract->getDecimal()
        );
        $amount = StrHelper::trimZeros($amount);

        if (bccomp($amount, self::CHECKER_RANGE_FROM) < 0)
            return;

        $this->walletService->addTransactions($data, $coinContract);
    }
}
