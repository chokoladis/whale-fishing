<?php

namespace App\Service\External\Alchemy;

use App\Config\External\AlchemyConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    protected const string BASE_URL = AlchemyConfig::BASE_URL;

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    )
    {
    }
}
