<?php

namespace App\Service\External\ModuleIO;

use App\Config\External\ModuleIOConfig;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    protected const BASE_URL = ModuleIOConfig::BASE_URL;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    )
    {
    }
}
