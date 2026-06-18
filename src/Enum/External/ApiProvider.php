<?php

namespace App\Enum\External;

enum ApiProvider : string
{
    case ALCHEMY = 'alchemy';
    case COINGECKO = 'coingecko';

    static function getRand(?self $exclude = null)
    {
        $cases = self::cases();
        if ($exclude) {
            foreach ($cases as $key => $case) {
                if ($case === $exclude) {
                    unset($cases[$key]);
                }
            }
        }

        return $cases[rand(0, count($cases)-1)];

    }
}
