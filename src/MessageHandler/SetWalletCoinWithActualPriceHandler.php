<?php

namespace App\MessageHandler;

use App\Enum\Coin\StableCoins;
use App\Enum\Coin\TransactionType;
use App\Helper\StrHelper;
use App\Messages\SetWalletCoinWithActualPriceMessage;
use App\Repository\WalletCoinRepository;
use App\Service\External\CoinPrice\MobulaIOService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SetWalletCoinWithActualPriceHandler
{
    public function __construct(
        private WalletCoinRepository $walletCoinRepository,
        private MobulaIOService  $mobulaIOService,
        #[Autowire(service: 'monolog.logger.services')]
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
    ){
    }

    public function __invoke(SetWalletCoinWithActualPriceMessage $message): void
    {
        $this->em->wrapInTransaction(function () use ($message) {

            $walletCoin = $this->walletCoinRepository->find($message->walletCoinId, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            if (!$walletCoin)
                return;

            $coin = $walletCoin->getCoin();
            $stableCoin = StableCoins::tryFrom($coin->getSymbol());

            $coinContract = $coin->getCoinContract()->current();
            $decimal = $coinContract->getDecimal();

            if ($stableCoin) {
                $price = 1;
            } else {
                $coinContract = $coin->getCoinContract()->first();
                $coinHistoryData = $this->mobulaIOService->getHistoryPrice($coinContract, $message->dataTime);

                $price = $coinHistoryData->price;
            }

            $currentBalance = StrHelper::trimZeros($walletCoin->getBalance());

            if ($message->type === TransactionType::OUT) {
                return;
            }

            if (bccomp($currentBalance, '0', $decimal) === 0) {
                return;
            }
            $oldAvgPrice = $walletCoin->getAvgPrice() ?: '0';
            $oldBalance = bcsub($currentBalance, $message->amount, $decimal);

            $numerator = bcadd(
                bcmul($oldAvgPrice, $oldBalance, $decimal),
                bcmul($price, $message->amount, $decimal),
                $decimal
            );

            try {
                $walletCoin->setAvgPrice(StrHelper::trimZeros(bcdiv($numerator, $currentBalance, $decimal)));
                $this->walletCoinRepository->save($walletCoin);
            } catch (\Throwable $e) {
                $this->logger->error('set wallet avg price, error', [$e->getMessage(), $e->getLine()]);
            }
        });
    }

}