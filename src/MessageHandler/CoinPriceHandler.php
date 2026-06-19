<?php

namespace App\MessageHandler;

use App\Entity\Coin;
use App\Exception\External\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Messages\UpdateCoinPrice;
use App\Repository\CoinRepository;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CoinPriceHandler
{
    /** @var array{string, GetterPriceInterface} $apiServiceProviders */
    function __construct(
        private CoinRepository  $coinRepository,
        #[Autowire(service: 'monolog.logger.priceUpdater')]
        protected LoggerInterface $logger,
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

        if ($coin->getPrice() === 0.0 || time() - $coin->getUpdatedAt()->getTimestamp() > 3600) {

            $provider = current($this->apiServiceProviders);

            $setting = $this->settingService->getCurrentApiPriceProvider();
            if ($setting?->getValue()) {
                $provider = $this->apiServiceProviders[$setting->getValue()];
                $this->logger->debug('new provider', ['provider' => $provider]);
            }

            // todo check
            $this->logger->info('try get price');
            try {
                $price = $provider->getPriceByContractAddress($message->contractAddress);
                $this->logger->info('getting price', ['price' => $price]);
            } catch (HttpException | RateLimitException $e) {
                $newSetting = $this->settingService->updateApiProviderForPrice($setting);
                $provider = $this->apiServiceProviders[$newSetting->getValue()];
                $price = $provider->getPriceByContractAddress($message->contractAddress);
                $this->logger->info('getting price by second provider', ['price' => $price]);
            } catch (\Throwable $e) {
                $this->settingService->updateApiProviderForPrice($setting);
                $this->logger->emergency('throw in coin price handler', [$e->getMessage(), $e->getTraceAsString()]);
                die();
            }

            $this->logger->info('result new price', ['price' => $price]);

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
