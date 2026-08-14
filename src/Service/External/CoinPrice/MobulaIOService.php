<?php

declare(strict_types=1);

namespace App\Service\External\CoinPrice;

use App\Config\External\MobulaIOConfig;
use App\DTO\Http\Response\Coin\BatchPricesBody;
use App\DTO\Http\Response\Coin\CoinContractResponse;
use App\DTO\Http\Response\Coin\CoinHistoryDataResponse;
use App\DTO\Http\Response\Coin\CoinStatisticsResponse;
use App\Entity\CoinContract;
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
        protected LoggerInterface     $logger,
        protected CoinRepository      $coinRepository,
        protected SerializerInterface $serializer,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }

    public function getCoinDetail(string $network, string $contractAddress): \App\DTO\Http\Response\Coin\CoinDetailResponse
    {
        //        "/api/1/market/data?shouldFetchPriceChange=24h&blockchain=ethereum&asset=cult"
        $this->validateNetworkAndContract($network, $contractAddress);

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/api/1/market/data?blockchain=%s&asset=%s', MobulaIOConfig::BASE_URL, $network, $contractAddress),
                ['headers' => ['Authorization' => $this->apiKey,]]
            );
            // todo переделать в dto?
            $responseBody = json_decode($response->getContent(), true);
            if (empty($responseBody['data']))
                throw new \Exception('Пустой ответ');

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);
            $this->logger->error('mobulaIO [priceService] class', [get_class($error)]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        //        todo save from all address? save logo
        //    {"data":
        //      { "logo":"https://metadata.mobula.io/assets/logos/evm_1_0xdac17f958d2ee523a2206206994597c13d831ec7.webp",
        //        ! contracts больше 15
        //          "contracts":[
        //              {"address":"0x55d398326f99059ff775485246999027b3197955","blockchainId":"56","blockchain":"BNB Smart Chain (BEP20)","decimals":18},
        //          ],

        $data = $responseBody['data'];
        $this->logger->debug('mobulaIO [priceService] price', [$data['price']]);

        return new \App\DTO\Http\Response\Coin\CoinDetailResponse(
            $data['name'],
            $data['symbol'],
            $data['decimals'],
            StrHelper::trimZeros(bcadd(StrHelper::toPlainDecimalString($data['price'], $data['decimals']), '0', $data['decimals'])),
            new CoinStatisticsResponse(
                $data['market_cap'],
                strval($data['volume']),
                strval($data['liquidity']),
                $data['total_supply'],
                strval($data['circulating_supply']),
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
            $this->logger->error('mobulaIO get price by symbol error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

//        $this->logger->debug('moduleIO получение по символу response', ['content' => $responseBody]);

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
                $data['market_cap'],
                strval($data['volume']),
                strval($data['liquidity']),
                $data['total_supply'],
                strval($data['circulating_supply']),
                $data['max_supply']
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
            if (empty($responseBody['data']))
                throw new \Exception('Пустой ответ');

        } catch (\Throwable $error) {
            $this->logger->error('mobulaIO get price by symbol error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

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

        return new \App\DTO\Http\Response\Coin\CoinHistoryDataResponse(
            $data['symbol'],
            StrHelper::toPlainDecimalString($data['priceUSD']),
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
            $this->logger->error('mobulaIO get price by symbol error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

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
