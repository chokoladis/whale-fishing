<?php

namespace App\MessageHandler;

use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Entity\CoinDetail;
use App\Messages\UpdateCoinPriceMessage;
use App\Repository\CoinContractRepository;
use App\Repository\CoinDetailRepository;
use App\Repository\CoinRepository;
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
        private CoinRepository $coinRepository,
        private CoinDetailRepository $coinDetailRepository,
        protected LoggerInterface $logger,
        private MobulaOService $mobulaPriceService,
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

                $this->fullUpdateCoin($coinDetailResponse);
            } catch (\Throwable $e) {
                $this->logger->emergency('throw in coin price handler', [$e->getMessage(), $e->getTraceAsString()]);
                return;
            }
        }
    }

    private function fullUpdateCoin(\App\DTO\Http\Response\Coin\CoinDetailResponse $coinDetailResponse): void
    {
        $this->logger->info('full update coin', ['response' => $coinDetailResponse]);
        //        todo in one transaction
        $this->coinContractRepository->updatePrice($this->coinContract,  $coinDetailResponse->price);

        if ($this->coin->getName() !== $coinDetailResponse->name) {
            $this->coin->setName($coinDetailResponse->name);
        }
        $this->coinRepository->updatePrice($this->coin, $coinDetailResponse->price);

        $stats = $coinDetailResponse->statistics;

        $coinDetail = new CoinDetail();
        $coinDetail->setCoin($this->coin);
        $coinDetail->setMarketCap($stats->marketCap);
        $coinDetail->setVolume($stats->volume);
        $coinDetail->setLiquidity($stats->liquidity);
        $coinDetail->setTotalSupply($stats->totalSupply);
        $coinDetail->setCirculationSupply($stats->circulationSupply);

        $this->coinDetailRepository->save($coinDetail);
    }
}
