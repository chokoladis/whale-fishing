<?php

namespace App\MessageHandler;

use App\Enum\Coin\StableCoins;
use App\Helper\StrHelper;
use App\Messages\SetWalletCoinWithActualPriceMessage;
use App\Repository\WalletCoinRepository;
use App\Service\External\CoinPrice\MobulaIOService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SetWalletCoinWithActualPriceHandler
{
    public function __construct(
        private WalletCoinRepository $walletCoinRepository,
        private MobulaIOService  $mobulaIOService,
        private LoggerInterface $logger
    ){

    }

    public function __invoke(SetWalletCoinWithActualPriceMessage $message): void
    {
        $walletCoin = $this->walletCoinRepository->findOneBy(['id' => $message->walletCoinId]);

        if (!$walletCoin)
            return;


        $coin = $walletCoin->getCoin();
        $stableCoin = StableCoins::tryFrom($coin->getSymbol());

        if ($stableCoin) {
            $newPrice = 1;
        } else {
            $coinContract = $coin->getCoinContract()->first();
            $coinHistoryData = $this->mobulaIOService->getHistoryPrice($coinContract, $message->dataTime);

            $newPrice = bcadd($walletCoin->getAvgPrice(), $coinHistoryData->price);
        }

        try {
            $walletCoin->setAvgPrice(StrHelper::trimZeros(bcdiv($newPrice, $walletCoin->getBalance())));

            $this->walletCoinRepository->save($walletCoin);
        } catch (\Throwable $e) {
            $this->logger->error('set wallet avg price, error', [$e->getMessage(), $e->getLine()]);
        }
    }

}