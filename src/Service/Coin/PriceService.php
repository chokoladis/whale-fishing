<?php

namespace App\Service\Coin;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Exception\External\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use App\Resource\CoinResource;
use App\Tool\SettingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PriceService
{
    public function __construct(
        private CoinRepository $coinRepository,
        private array $apiServiceProviders,
        private SettingService $settingService,
    )
    {
    }

    public function getPriceBySymbol(string $symbol)
    {
        $symbol = strtoupper(trim($symbol));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $coin = $this->coinRepository->findOneBy(['symbol' => $symbol]);
        if (empty($coin)) {
            /** @var GetterPriceInterface $provider */
            $provider = current($this->apiServiceProviders);

            $setting = $this->settingService->getCurrentApiPriceProvider();
            if ($setting?->getValue()) {
                $provider = $this->apiServiceProviders[$setting->getValue()];
            }

            try {
                $price = $provider->getPriceBySymbol($symbol);
            } catch (HttpException | RateLimitException $e) {
                $newSetting = $this->settingService->updateApiProviderForPrice($setting);
                $provider = $this->apiServiceProviders[$newSetting->getValue()];
                $price = $provider->getPriceBySymbol($symbol);
            } catch (\Throwable $e) {
                $this->settingService->updateApiProviderForPrice($setting);
                return;
            }

            $this->coinRepository->updatePrice(
                $coin,
                $price
            );
        }

        return $coin;
    }


}
