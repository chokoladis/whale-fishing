<?php

declare(strict_types=1);

namespace App\Service\External\CoinPrice;

use App\Config\External\MobulaIOConfig;
use App\DTO\Http\Response\Coin\BatchPricesBody;
use App\DTO\Http\Response\Coin\CoinContractResponse;
use App\DTO\Http\Response\Coin\CoinHistoryDataResponse;
use App\DTO\Http\Response\Coin\CoinStatisticsResponse;
use App\Entity\CoinContract;
use App\Enum\External\ChainId;
use App\Enum\External\Network;
use App\Exception\RateLimitException;
use App\Helper\StrHelper;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MobulaIOService extends BaseService
{

    public function __construct(
        #[Autowire(env: 'MOBULAIO_API_KEY')]
        protected string              $apiKey,
        protected HttpClientInterface $httpClient,
        #[Autowire(service: 'monolog.logger.services')]
        protected LoggerInterface     $logger,
        protected CoinRepository      $coinRepository,
        protected SerializerInterface $serializer,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }

    public function getCoinDetail(string $network, string $contractAddress): \App\DTO\Http\Response\Coin\CoinDetailResponse
    {
        $this->validateNetworkAndContract($network, $contractAddress);

        $chainId = ChainId::getByNetwork(Network::from($network));

        try {
            $response = $this->httpClient->request(
                'GET',
                urldecode(sprintf('%s/api/2/token/markets?blockchain=%s&address=%s&limit=1', MobulaIOConfig::BASE_URL, $chainId->value, $contractAddress)),
                ['headers' => ['Authorization' => $this->apiKey,]]
            );
            // todo переделать в dto?
            $responseBody = json_decode($response->getContent(), true);

            if (empty($responseBody['data']))
                throw new \Exception('Пустой ответ', 404);

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO [priceService] error getcoinDetail', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        // todo save from all address? save logo
        $data = current($responseBody['data']);

        $this->logger->info('MobulaIO response in market/data', [$data]);

        $actualData = $data['base']['address'] === $contractAddress ? $data['base'] : $data['quote'];

        return new \App\DTO\Http\Response\Coin\CoinDetailResponse(
            $actualData['name'],
            $actualData['symbol'],
            $actualData['decimals'],
            StrHelper::trimZeros(bcadd(StrHelper::toNormalNum($actualData['priceUSD']), '0', $actualData['decimals'])),
            new CoinStatisticsResponse(
                marketCap: $actualData['marketCapUSD'],
                liquidity: !empty($actualData['liquidityUSD']) ? strval($actualData['liquidityUSD']) : strval($data['liquidityUSD']) ?? '',
                totalSupply:  $actualData['totalSupply'],
                circulationSupply: strval($actualData['circulatingSupply']),
                volume24: !empty($actualData['volume24hUSD']) ? strval($actualData['volume24hUSD']) : strval($data['volume24hUSD']) ?? '',
            )
        );
    }

    public function getCoinDetailBySymbol(string $symbol): \App\DTO\Http\Response\Coin\CoinDetailResponse
    {
        try {
            $response = $this->httpClient->request(
                'GET',
//                sprintf('%s/api/2/fast-search?input=%s', MobulaIOConfig::BASE_URL, $symbol),
                sprintf('%s/api/1/metadata?symbol=%s', MobulaIOConfig::BASE_URL, $symbol),
                ['headers' => ['Authorization' => $this->apiKey,]]
            );
            // todo переделать в dto?
            $responseBody = json_decode($response->getContent(), true);
            if (empty($responseBody['data']))
                throw new \Exception('Пустой ответ');

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO get coinDetail by symbol error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        $this->logger->debug('moduleIO получение по символу response', ['content' => $responseBody]);

//        todo save from all address? save logo
        $data = $responseBody['data'];

        $decimals = max($data['decimals']);

        $contracts = [];

        foreach ($data['blockchains'] as $idx => $blockchain) {
            if ($network = Network::tryFrom(strtolower($blockchain))) {
                $contracts[] = new CoinContractResponse(
                    $data['contracts'][$idx],
                    $network->value,
                    $data['decimals'][$idx],
                );
            }
        }

        return new \App\DTO\Http\Response\Coin\CoinDetailResponse(
            $data['name'],
            strtoupper($data['symbol']),
            $decimals,
            StrHelper::trimZeros(strval($data['price'])),
            new CoinStatisticsResponse(
                marketCap: $data['market_cap'],
                liquidity: strval($data['liquidity']),
                totalSupply: $data['total_supply'],
                circulationSupply: strval($data['circulating_supply']),
                volume: strval($data['volume']),
                maxSupply: $data['max_supply']
            ),
            $contracts
        );
    }

    public function getHistoryPrice(CoinContract $coinContract, \DateTimeImmutable $dateTime) : CoinHistoryDataResponse
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/api/2/token/price-at?chainId=%s&address=%s&timestamp=%d',
                    MobulaIOConfig::BASE_URL, $coinContract->getNetwork(), $coinContract->getContractAddress(), $dateTime->getTimestamp()
                ),
                ['headers' => ['Authorization' => $this->apiKey,]]
            );
            $responseBody = json_decode($response->getContent(), true);
            $this->logger->debug('get history response', [$response->getContent()]);

            if (empty($responseBody['data']))
                throw new \Exception('Пустой ответ');

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO get history price by contract error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        $this->logger->debug('moduleIO получение исторических данных', ['content' => $responseBody]);

        $data = $responseBody['data'];

        $price = StrHelper::toNormalNum($data['priceUSD']);
        $dotPos = stripos($price, '.');

        return new \App\DTO\Http\Response\Coin\CoinHistoryDataResponse(
            $data['symbol'],
            $price,
            strlen(substr($price, ++$dotPos)),
            strval($data['marketCapUSD']),
        );
    }

    /**
     * @param array<string> $addresses
     * @param array<string> $networks
     * @return BatchPricesBody
     * @throws RateLimitException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Throwable
     */
    public function batchPrices(array $data)
    {
        try {

            $response = $this->httpClient->request(
                'POST',
                sprintf('%s/api/2/token/price',
                    MobulaIOConfig::BASE_URL
                ),
                [
                    'headers' => ['Authorization' => $this->apiKey,],
                    'json' => ['items' => $data]
                ]
            );

            $this->logger->info('batch response', [$response->getContent()]);

            return $this->serializer->deserialize($response->getContent(), BatchPricesBody::class, 'json');

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO batch prices error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }
    }
}
