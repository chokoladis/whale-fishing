<?php

namespace App\Service\External\CoinGecko;

use App\Config\External\CoinGeckoConfig;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    protected const BASE_URL = CoinGeckoConfig::BASE_URL;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    )
    {
    }
}
