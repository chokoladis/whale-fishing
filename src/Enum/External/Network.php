<?php

namespace App\Enum\External;

enum Network : string
{
    case ETHEREUM = 'ETH';
    case POLYGON = 'MATIC';
    case OPTIMISM = 'OPT';
    case ARBITRUM = 'ARB';
    case BASE = 'BASE';
    case SOLANA = 'SOL';
}
