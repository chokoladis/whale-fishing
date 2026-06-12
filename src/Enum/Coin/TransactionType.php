<?php

namespace App\Enum\Coin;

enum TransactionType : string
{
    case IN = 'in';
    case OUT = 'out';
}
