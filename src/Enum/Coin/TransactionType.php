<?php

namespace App\Enum\Coin;

enum TransactionType : string
{
    case BUY = 'buy';
    case SELL = 'sell';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
}
