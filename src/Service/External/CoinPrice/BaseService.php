<?php

namespace App\Service\External\CoinPrice;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Interface\External\GetCoinDetailInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class BaseService implements GetCoinDetailInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    )
    {
    }

    public function validateNetworkAndContract(string $network, string $contractAddress) : true
    {
        $network = trim($network);
        $contractAddress = trim($contractAddress);
        if (!mb_strlen($contractAddress) || !mb_strlen($network)) {
            throw new InvalidCoinSymbolException('contractAddress or network cannot be empty.');
        }

        return true;
    }
}
