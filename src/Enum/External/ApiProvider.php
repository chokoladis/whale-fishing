<?php

namespace App\Enum\External;

enum ApiProvider : string
{
    case ALCHEMY = 'ALCHEMY';
    case COINGECKO = 'COINGECKO';

    static function getNewProvider(?self $current = null) : self
    {
        if ($current) {
            return $current === self::ALCHEMY ? self::COINGECKO : self::ALCHEMY;
        } else {
            return current(self::cases());
        }
    }
}
