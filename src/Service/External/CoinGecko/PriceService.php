<?php

namespace App\Service\External\CoinGecko;

use App\Exception\Coin\InvalidCoinSymbolException;
use App\Interface\External\GetterPriceInterface;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService extends \App\Service\External\CoinGecko\ClientService implements GetterPriceInterface
{

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
    )
    {
        parent::__construct($this->httpClient, $this->logger);
    }

    public function getPriceBySymbol(string $symbol) : float
    {
        $symbol = strtoupper(trim($symbol));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $httpRequest = HttpClient::create();
        $response = $httpRequest->request('GET',
//            https://api.coingecko.com/api/v3/simple/token_price/ethereum?contract_addresses=0x514910771af9ca656af840dff83e8264ecf986ca&vs_currencies=usd
//            ethereum?contract_addresses=0x514910771af9ca656af840dff83e8264ecf986ca&vs_currencies=usd
            sprintf('/api/v3/simple/token_price/%s', self::BASE_URL, $this->alchemyApiKey, $symbol)
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
//        todo
        return 0.0;
    }


}
