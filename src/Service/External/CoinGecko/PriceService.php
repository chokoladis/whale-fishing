<?php

namespace App\Service\External\CoinGecko;

use App\Config\External\CoinGeckoConfig;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Exception\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService extends ClientService implements GetterPriceInterface
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

    public function getPriceByNetworkAndAddress(string $network, string $contractAddress) : float
    {
        // todo вынести в базовый класс?
//        https://api.coingecko.com/api/v3/simple/token_price/{network}?contract_addresses={contract_address}&vs_currencies=usd
//        Если это нативная монета (contract_address === 'native'), то дергаешь их обычный эндпоинт .../simple/price?ids={network_id}

        $network = trim($network);
        $contractAddress = trim($contractAddress);
        if (!mb_strlen($contractAddress) || !mb_strlen($network)) {
            throw new InvalidCoinSymbolException('$contractAddress or $network cannot be empty.');
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
