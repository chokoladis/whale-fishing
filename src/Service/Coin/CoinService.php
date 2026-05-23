<?php

declare(strict_types=1);

namespace App\Service\Coin;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoinService
{
    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        private string $alchemyApiKey
    )
    {
    }

    public function getCoins(string $coinName)
    {
    }
}
