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

    public function updateApiProviderForPrice(?Setting $setting) : ?Setting
    {
        $newValue = ApiProvider::getNewProvider();

        if ($setting) {
            $currentApiProvider = ApiProvider::from($setting->getValue());
            $newValue = ApiProvider::getNewProvider($currentApiProvider);
        } else {
            $setting = new Setting();
            $setting->setName('external_api_service.price');
        }

        $setting->setValue($newValue->name ?? $setting->getValue());

        $this->settingRepository->save($setting);

        return $setting;
    }

    public function getCurrentApiPriceProvider() : ?Setting
    {
        return $this->settingRepository->getCurrentApiPriceProvider();
    }
}
