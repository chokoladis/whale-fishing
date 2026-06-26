<?php

namespace App\Service\External\Alchemy;

use App\Exception\RateLimitException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use App\Tool\SettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $symbol = trim($symbol);
        if (!mb_strlen($symbol)) {
            throw new BadRequestException('Symbol cannot be empty.');
        }

        $httpRequest = HttpClient::create();
        $response = $httpRequest->request('GET',
            sprintf('%s/prices/v1/%s/tokens/by-symbol?symbols=%s', self::BASE_URL, $this->alchemyApiKey, $symbol)
        );

        $responseBody = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody['data'])) {
            $data = current($responseBody['data']);

            if (!empty($data['prices'])){
                return floatval(current($data['prices'])['value']);
            }
        }

        $this->logger->error('alchemy [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);

        throw new HttpException($response->getStatusCode() !== Response::HTTP_OK
            ? $response->getStatusCode()
            : Response::HTTP_NOT_FOUND,
            'Не удалось получить данные из стороннего ресурса'
        );
    }

    public function getPriceByContractAddress(string $contractAddress) : float
    {
        $contractAddress = trim($contractAddress);
        if (!mb_strlen($contractAddress)) {
            // create new exception
            throw new BadRequestException('contactAddress cannot be empty.');
        }

        $httpRequest = HttpClient::create();
        try {
            $response = $httpRequest->request('POST',
                sprintf('%s/prices/v1/%s/tokens/by-address', self::BASE_URL, $this->alchemyApiKey),
                [ 'json' => ['addresses' => [['network' => 'eth-mainnet', 'address' => $contractAddress]]]]
            );

            $responseBody = json_decode($response->getContent(false), true);
        } catch (\Throwable $error) {
            $this->logger->error('alchemy [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException(previous: $error);
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        $this->logger->alert('result alchemy price', ['content' => $responseBody, 'status' => $response->getStatusCode()]);

        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody['data'])) {
            $data = current($responseBody['data']);

            if (!empty($data['error'])) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $data['error']['message']);
            }

            return floatval(current($data['prices'])['value']);
        } else {
            $this->logger->error('alchemy [priceService] error', ['content' => $response->getContent(false), 'status' => $response->getStatusCode()]);

            throw new HttpException($response->getStatusCode(), 'Не удалось получить данные из стороннего ресурса');
        }
    }

}
