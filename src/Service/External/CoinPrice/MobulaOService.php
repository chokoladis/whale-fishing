<?php

namespace App\Service\External\CoinPrice;

use App\Config\External\MobulaIOConfig;
use App\Exception\RateLimitException;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MobulaOService extends BaseService
{
    protected const BASE_URL = MobulaIOConfig::BASE_URL;

    public function __construct(
        #[Autowire(env: 'MOBULAIO_API_KEY')]
        protected string $apiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }


//    public function somecode(string $symbol)
//    {
//        $symbol = trim($symbol);
//        if (!mb_strlen($symbol)) {
//            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
//        }
//
//        try {
//            $response = $this->httpClient->request('GET', sprintf('%s/api/1/metadata?symbol=%s', self::BASE_URL, $symbol),
//                [
//                    'headers' => ['Authorization' => $this->apiKey,]
//                ]);
//            $responseBody = json_decode($response->getContent(false), true);
//        } catch (\Throwable $error) {
//            $this->logger->error('moduleIO [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);
//
//            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
//                throw new RateLimitException();
//            } else if ($error->getCode() === 0) {
//                // need reconnect
//                exit();
//            }
//
//            throw $error;
//        }
//
//        $this->logger->debug('moduleIO [priceService] response', ['content' => $responseBody]);
//        dd();
//    }

    public function getPriceByNetworkAndAddress(string $network, string $contractAddress) : float
    {
        //        "/api/1/market/data?shouldFetchPriceChange=24h&blockchain=ethereum&asset=cult"
        $this->validateNetworkAndContract($network, $contractAddress);

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s//api/1/market/data?shouldFetchPriceChange=24h&blockchain=%s&asset=%s', self::BASE_URL, $network, $contractAddress),
                ['headers' => ['Authorization' => $this->apiKey,]]
            );
            $responseBody = json_decode($response->getContent(false), true);
        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

//        todo next
        $this->logger->debug('moduleIO [priceService] response', ['content' => $responseBody]);
        dd();

    }
}
