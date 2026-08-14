<?php

namespace App\Enum\External;

enum ChainId : string
{
    case Ethereum = 'evm:1';
    case Optimism = 'evm:10';
    case BinanceSmartChain = 'evm:56';
    case Polygon = 'evm:137';
    case Arbitrum = 'evm:42161';
    case BASE = 'evm:8453';
    case Avalanche = 'evm:43114';
}
