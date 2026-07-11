<?php

namespace App\MessageHandler;

use App\Entity\Coin;
use App\Enum\Coin\NativeCoins;
use App\Exception\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Messages\UpdateCoinPriceMessage;
use App\Repository\CoinRepository;
use App\Service\External\ModuleIO\PriceService;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateCoinPriceHandler
{
    /** @param array{string, GetterPriceInterface} $apiServiceProviders */
    function __construct(
        private CoinRepository  $coinRepository,
        #[Autowire(service: 'monolog.logger.priceUpdater')]
        protected LoggerInterface $logger,
//        private array $apiServiceProviders,
        private SettingService $settingService,
        private PriceService $priceService,
    )
    {
    }

    public function __invoke(UpdateCoinPriceMessage $message) : void
    {
        // index составной?
        /** @var ?Coin $coin */
        $coin = $this->coinRepository->findOneBy([
            'symbol' => $message->symbol,
            'contractAddress' => $message->contractAddress,
        ]);

        if (!$coin) {
            $coin = new Coin();
            $coin->setName($message->symbol);
            $coin->setSymbol($message->symbol);
//            $coin->setNetwork($message->contractAddress);
//            $coin->setContractAddress($message->contractAddress);
        }

        if ($nativeCoin = NativeCoins::tryFrom($message->symbol)) {

        } else {

        }


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
                return;
            }

            $this->logger->info('result new price', ['price' => $price]);
        }

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
