<?php

namespace App\Service\External\Alchemy;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Exception\External\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService extends ClientService implements GetterPriceInterface
{
    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
        protected SettingService $settingService,
    )
    {
        parent::__construct($this->alchemyApiKey, $this->httpClient, $this->logger);
    }

    public function getPriceBySymbol(string $symbol) : float
    {
        $symbol = strtoupper(trim($symbol));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $httpRequest = HttpClient::create();
        $response = $httpRequest->request('GET',
            sprintf('%s/prices/v1/%s/tokens/by-symbol?symbols=%s', self::BASE_URL, $this->alchemyApiKey, $symbol)
        );

        $responseBody = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody['data'])) {
            $data = current($responseBody['data']);

            return floatval(current($data['prices'])['value']);
//            $coin = (new Coin())
//                ->setSymbol($data['symbol'])
//                ->setName($data['symbol'])
//                ->setPrice(floatval(current($data['prices'])['value']));

//            todo contractAddress
//            $this->coinRepository->save($coin);
        } else {
            $this->logger->error('alchemy [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new \HttpException('Не удалось получить данные из стороннего ресурса');
        }
    }

    public function getPriceByContractAddress(string $contractAddress) : float
    {
        $contractAddress = trim($contractAddress);
        if (!mb_strlen($contractAddress)) {
            // create new exception
            throw new InvalidCoinSymbolException('contactAddress cannot be empty.');
        }

        $httpRequest = HttpClient::create();
        try {
            $response = $httpRequest->request('POST',
                sprintf('%s/prices/v1/%s/tokens/by-address', self::BASE_URL, $this->alchemyApiKey),
                [ 'json' => ['addresses' => ['network' => 'eth-mainnet', 'address' => $contractAddress]]]
            );
        } catch (\Throwable $error) {
            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException('alchemy');
            }

            $this->logger->error('alchemy [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            throw $error;
        }

        $responseBody = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody['data'])) {
            $data = current($responseBody['data']);

            return floatval(current($data['prices'])['value']);
        } else {
            $this->logger->error('alchemy [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new \HttpException('Не удалось получить данные из стороннего ресурса');
        }
    }

}
