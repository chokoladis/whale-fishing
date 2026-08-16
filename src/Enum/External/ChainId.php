<?php

namespace App\Enum\External;

enum ChainId : string
{
    case ETHEREUM = 'evm:1';
    case OPTIMISM = 'evm:10';
    case BinanceSmartChain = 'evm:56';
    case POLYGON = 'evm:137';
    case ARBITRUM = 'evm:42161';
    case BASE = 'evm:8453';
//    case Avalanche = 'evm:43114';


    public static function getByNetwork(Network $network)
    {
        return match ($network) {
            Network::ETHEREUM => ChainId::ETHEREUM,
            Network::OPTIMISM => ChainId::OPTIMISM,
            Network::POLYGON => ChainId::POLYGON,
            Network::ARBITRUM => ChainId::ARBITRUM,
            Network::BASE => ChainId::BASE,
        };
    }
}
