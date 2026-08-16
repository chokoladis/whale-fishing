<?php

namespace App\Helper;

use App\Config\External\AlchemyConfig;
use App\Enum\External\Network;

class NetworkHelper
{
    static function getDomainByNetwork(Network $network)
    {
        return match ($network) {
            Network::ETHEREUM => AlchemyConfig::ETH_MAINNET_DOMAIN,
            Network::POLYGON => AlchemyConfig::POLYGON_MAINNET_DOMAIN,
            Network::OPTIMISM => AlchemyConfig::OPTIMISM_MAINNET_DOMAIN,
            Network::ARBITRUM => AlchemyConfig::ARBITRUM_MAINNET_DOMAIN,
            Network::BASE => AlchemyConfig::BASE_MAINNET_DOMAIN,
//            Network::SOLANA => AlchemyConfig::SOLANA_MAINNET_DOMAIN,
        };
    }
}