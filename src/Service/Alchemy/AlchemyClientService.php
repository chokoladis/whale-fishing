<?php

namespace App\Service\Alchemy;

use App\Config\External\AlchemyConfig;
use App\Repository\CoinRepository;
use App\Resource\CoinResource;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AlchemyClientService
{
    protected const BASE_URL = AlchemyConfig::BASE_URL;

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    )
    {
    }
}
