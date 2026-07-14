<?php

namespace App\Service\External\CoinPrice;

use App\Config\External\MobulaIOConfig;
use App\DTO\Http\Response\Coin\CoinStatisticsResponse;
use App\Exception\RateLimitException;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MobulaOService extends BaseService
{

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

    public function getCoinDetail(string $network, string $contractAddress) : \App\DTO\Http\Response\Coin\CoinDetailResponse
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

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

//        $this->logger->debug('moduleIO [priceService] response', ['content' => $responseBody]);
        //        todo save from all address? save logo
        //    {"data":
        //      { "logo":"https://metadata.mobula.io/assets/logos/evm_1_0xdac17f958d2ee523a2206206994597c13d831ec7.webp",
        //        ! contracts больше 15
        //          "contracts":[
        //              {"address":"0x55d398326f99059ff775485246999027b3197955","blockchainId":"56","blockchain":"BNB Smart Chain (BEP20)","decimals":18},
        //          ],
        $data = $responseBody['data'];

        return new \App\DTO\Http\Response\Coin\CoinDetailResponse(
            $data['name'],
            $data['symbol'],
            $data['decimals'],
            $data['price'],
            new CoinStatisticsResponse(
                $data['market_cap'],
                $data['volume'],
                $data['liquidity'],
                $data['total_supply'],
                $data['circulating_supply'],
            )
        );
    }
}
