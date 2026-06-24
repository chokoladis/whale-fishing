<?php

namespace App\Service\External\CoinGecko;

use App\Config\External\CoinGeckoConfig;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Exception\External\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService extends \App\Service\External\CoinGecko\ClientService implements GetterPriceInterface
{

    public function __construct(
        #[Autowire(env: 'COINGECKO_API_KEY')]
        protected string $apiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }

    public function getPriceBySymbol(string $symbol) : float
    {
        $symbol = trim($symbol);
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        try {
            $response = $this->httpClient->request('GET',
                sprintf('%s/api/v3/simple/price?vs_currencies=usd&symbols=%s', CoinGeckoConfig::BASE_URL, $symbol),
                [ 'headers' => ['x-cg-demo-api-key' => $this->apiKey]]
            );

            $responseBody = json_decode($response->getContent(false), true);
        } catch (\Throwable $error) {
            $this->logger->error('coingecko [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }


        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody[$symbol])) {
            return floatval($responseBody[$symbol]['usd']);
        } else {
            $this->logger->error('coin gecko [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new HttpException(Response::HTTP_NOT_FOUND, 'Не удалось получить данные из стороннего ресурса');
        }
    }

    public function getPriceByContractAddress(string $contractAddress) : float
    {
        $contractAddress = trim($contractAddress);
        if (!mb_strlen($contractAddress)) {
            throw new InvalidCoinSymbolException('$contractAddress cannot be empty.');
        }

        try {
            $response = $this->httpClient->request('GET',
                sprintf('%s/api/v3/simple/token_price/ethereum?vs_currencies=usd&contract_addresses=%s', self::BASE_URL, $contractAddress)
            );

            $responseBody = json_decode($response->getContent(false), true);

        } catch (\Throwable $error) {
            $this->logger->error('coingecko [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            }

            throw $error;
        }

        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody[$contractAddress])) {;
            return floatval($responseBody[$contractAddress]['usd']);
        } else {
            $this->logger->error('coingecko [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new HttpException($response->getStatusCode(), 'Не удалось получить данные из стороннего ресурса');
        }
    }

}
