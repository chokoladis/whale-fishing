<?php

namespace App\MessageHandler;

use App\Entity\Coin;
use App\Exception\External\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Messages\UpdateCoinPrice;
use App\Repository\CoinRepository;
use App\Service\External\Alchemy\PriceService;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CoinPriceHandler
{
    /** @var array{string, GetterPriceInterface} $apiServiceProviders */
    function __construct(
        private PriceService    $coinService,
        private CoinRepository  $coinRepository,
        private LoggerInterface $logger,
        private array $apiServiceProviders,
        private SettingService $settingService,
    )
    {
    }

    public function __invoke(UpdateCoinPrice $message) : void
    {
        /** @var Coin $coin */
        $coin = $this->coinRepository->findOneBy([
            'contractAddress' => $message->contractAddress,
        ]);

        $this->logger->debug('current coin price', ['price' => $coin->getPrice()]);
        if ($coin->getPrice() === 0.0 || time() - $coin->getUpdatedAt()->getTimestamp() > 3600) {

            $setting = $this->settingService->getCurrentApiPriceProvider();

            /** @var ?GetterPriceInterface $provider */
            $provider = $this->apiServiceProviders[$setting?->getValue()];
            if (!$provider) {
                $provider = current($this->apiServiceProviders);
            }

//            todo check
            try {
                $price = $provider->getPriceByContractAddress($message->contractAddress);
            } catch (RateLimitException $e) {
//                todo change
                $this->settingService->updateApiProviderForPrice($setting);
            }

            $this->logger->info('new price', ['price' => $price]);

            try {
                $this->coinRepository->updatePrice(
                    $coin,
                    $price
                );
            } catch (\Throwable $exception) {
                $this->logger->error($exception->getMessage(), ['exception' => [$exception->getFile(), $exception->getLine()]]);
            }

        }
    }
}
