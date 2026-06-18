<?php

namespace App\Tool;

use App\Entity\Setting;
use App\Enum\External\ApiProvider;
use App\Repository\SettingRepository;

class SettingService
{

    public function __construct(
        protected SettingRepository $settingRepository,
    )
    {
    }

    public function updateApiProviderForPrice(?Setting $setting = null)
    {
        if ($setting) {

            $currentApiProvider = ApiProvider::tryFrom($setting?->getValue());

            if ($currentApiProvider) {
                $newValue = ApiProvider::getRand($currentApiProvider)->name;
            }
        } else {
            $setting = new Setting();
            $setting->setName('external_api_service.price');
            $newValue = ApiProvider::getRand()->name;
        }

        $setting->setValue($newValue);

        $this->settingRepository->update($setting);
    }

    public function getCurrentApiPriceProvider()
    {
        return $this->settingRepository->getCurrentApiPriceProvider();
    }
}
