<?php

namespace App\Service\External\ModuleIO;

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

class PriceService extends ClientService
{

    public function __construct(
        #[Autowire(env: 'MODULAIO_API_KEY')]
        protected string $apiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }


    public function somecode(string $symbol)
    {
        $symbol = trim($symbol);
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        try {
            $response = $this->httpClient->request('GET', sprintf('%s/api/1/metadata?symbol=%s', self::BASE_URL, $symbol),
            [
                'headers' => ['Authorization' => $this->apiKey,]
            ]);
            $responseBody = json_decode($response->getContent(false), true);
        } catch (\Throwable $error) {
            $this->logger->error('moduleIO [priceService] error', ['content' => $error->getMessage(), 'status' => $error->getCode()]);

            if ($error->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                throw new RateLimitException();
            } else if ($error->getCode() === 0) {
                // need reconnect
                exit();
            }

            throw $error;
        }

        $this->logger->debug('moduleIO [priceService] response', ['content' => $responseBody]);
        dd();

        // modula.io
//        "market_cap": 588637385.507558,
//      "liquidity": 2440736.32815497,
//      "volume": 17011062.0834084,
//      "description": "",

//      "total_supply": 10000000000,
//      "circulating_supply": 6370890086,
//      "max_supply": 10000000000,
//        "listed_at": "2023-03-17T01:48:09.000Z",

//        --
//        $pairs = $responseBody['pairs'];
//        if (empty($pairs)) {
//            throw new HttpException(Response::HTTP_NOT_FOUND, 'Не удалось получить данные из стороннего ресурса');
////            return null;
//        }

//    {
//      "chainId": "bsc",
//      "dexId": "pancakeswap",
//      "url": "https://dexscreener.com/bsc/0x9f599f3d64a9d99ea21e68127bb6ce99f893da61",
//      "pairAddress": "0x9F599F3D64a9D99eA21e68127Bb6CE99f893DA61",

//      "baseToken": {
//        "address": "0x2170Ed0880ac9A755fd29B2688956BD959F933F8",
//        "name": "Ethereum Token",
//        "symbol": "ETH"
//      },
//      "quoteToken": {
//        "address": "0x55d398326f99059fF775485246999027B3197955",
//        "name": "Tether USD",
//        "symbol": "USDT"
//      },
//      "priceNative": "1746.5180",
//      "priceUsd": "1746.51",

//      "txns": {
//        "m5": {
//          "buys": 13,
//          "sells": 18
//        },
//        "h1": {
//          "buys": 243,
//          "sells": 189
//        },
//        "h6": {
//          "buys": 2449,
//          "sells": 2150
//        },
//        "h24": {
//          "buys": 14356,
//          "sells": 11736
//        }
//      },
//      "volume": {
//        "h24": 3380684.77,
//        "h6": 678454.57,
//        "h1": 47855.59,
//        "m5": 2744.45
//      },
//      "priceChange": {
//        "h1": -0.14,
//        "h6": 0.28,
//        "h24": 0.51
//      },
//      "liquidity": {
//        "usd": 1326412.52,

//      },
//      "fdv": 881963260,
//      "marketCap": 881963260,
//      "pairCreatedAt": 1683365205000
//    },
//    {

//        if ($response->getStatusCode() === Response::HTTP_OK && !empty($responseBody[$symbol])) {
//            return floatval($responseBody[$symbol]['usd']);
//        } else {
//            $this->logger->error('coin gecko [priceService] error', ['content' => $response->getContent(), 'status' => $response->getStatusCode()]);
//

//        }
    }
}
