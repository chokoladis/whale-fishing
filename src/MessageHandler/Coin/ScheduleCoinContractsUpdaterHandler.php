<?php

namespace App\MessageHandler\Coin;

use App\Messages\Coin\ScheduleCoinContractsUpdaterMessage;
use App\Repository\CoinContractRepository;
use App\Service\Coin\CoinContractService;
use App\Service\External\CoinPrice\MobulaIOService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ScheduleCoinContractsUpdaterHandler
{

    function __construct(
        private CoinContractRepository $coinContractRepository,
        private MobulaIOService        $mobulaPriceService,
        private CoinContractService    $coinContractService,
        private LoggerInterface        $logger,
    )
    {
    }

    public function __invoke(ScheduleCoinContractsUpdaterMessage $message): void
    {
        try {
            $coinContracts = $this->coinContractRepository->getByUpdatedAtBefore(new \DateTimeImmutable('-5 minutes'));

            $preparedData = [];

            foreach ($coinContracts as $coinContract) {
                $preparedData[] = [
                    'address' => $coinContract->getContractAddress(),
                    'blockchain' => $coinContract->getNetwork(),
                ];

                $coinContracts[strtolower($coinContract->getNetwork() . '_' . $coinContract->getContractAddress())] = $coinContract;
            }

            $this->logger->info('schedule coin updater', $preparedData);

            if (empty($preparedData))
                return;

            $result = $this->mobulaPriceService->batchPrices($preparedData);

            //todo update with $collectionCoinContract and $result
            $this->coinContractService->updatePricesByBatchPrices($result, $coinContracts);
        } catch (\Throwable $e) {
            $this->logger->emergency('throw in schedule coin contracts handler', [$e->getMessage(), $e->getTraceAsString()]);
            return;
        }

        $this->logger->alert('Успешно обновленно');
    }
}
