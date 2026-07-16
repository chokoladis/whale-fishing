<?php

namespace App\Enum\External;

enum Network : string
{
    case ETHEREUM = 'ethereum';
    case POLYGON = 'polygon';
    case OPTIMISM = 'optimism';
    case ARBITRUM = 'arbitrum';
    case BASE = 'base';
    //    case SOLANA = 'solana';
    //
    //Optimistic","Avalanche C-Chain","BNB Smart Chain (BEP20)","Mantle - отдаются в mobula/api/1/metadata?symbol=%s
}
