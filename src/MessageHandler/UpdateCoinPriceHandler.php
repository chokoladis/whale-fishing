<?php

namespace App\MessageHandler;

use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Entity\CoinDetail;
use App\Messages\UpdateCoinPriceMessage;
use App\Repository\CoinContractRepository;
use App\Repository\CoinDetailRepository;
use App\Repository\CoinRepository;
use App\Service\Coin\CoinPriceService;
use App\Service\Coin\CoinService;
use App\Service\External\CoinPrice\MobulaOService;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateCoinPriceHandler
{
    private ?Coin $coin = null;
    private ?CoinContract $coinContract = null;

    function __construct(
        private CoinContractRepository $coinContractRepository,
        protected LoggerInterface $logger,
        private MobulaOService $mobulaPriceService,
        private CoinService $coinService,
    )
    {
    }

    public function __invoke(UpdateCoinPriceMessage $message) : void
    {
        /** @var ?CoinContract $coinContract */
        $coinContract = $this->coinContractRepository->findOneBy([
            'network' => $message->network,
            'contractAddress' => $message->contractAddress,
        ]);

        if (!$coinContract) {
            $this->logger->debug('Почему то не создался coin');
            return;
//            $coin = new Coin();
//            $coin->setName($message->symbol);
//            $coin->setSymbol($message->symbol);
//            $coin->setNetwork($message->contractAddress);
//            $coin->setContractAddress($message->contractAddress);
        }

        $this->coinContract = $coinContract;
        $this->coin = $coinContract->getCoin();

//        $this->logger->debug('invoke update coin price', ['coin price' => $this->coin->getAvgPrice(), 'coinContract' => $this->coinContract]);

//        todo?
//        if ($nativeCoin = NativeCoins::tryFrom($message->symbol)) {
//        }

        if (empty($this->coin->getAvgPrice()) || empty($this->coinContract->getLocalPrice())
            || time() - $this->coin->getUpdatedAt()->getTimestamp() > 3600) {

            $this->logger->info('try get price');
            try {
                $coinDetailResponse = $this->mobulaPriceService->getCoinDetail(
                    $message->network,
                    $message->contractAddress
                );

                $this->logger->info('getting price', ['response' => $coinDetailResponse]);

                $this->coinService->fullUpdateCoin($coinContract, $coinDetailResponse);
            } catch (\Throwable $e) {
                $this->logger->emergency('throw in coin price handler', [$e->getMessage(), $e->getTraceAsString()]);
                return;
            }
        }
    }
}
