<?php

namespace App\Service\Alchemy;

use App\Entity\Coin;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Repository\CoinRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriceService extends AlchemyClientService
{

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
    )
    {
        parent::__construct($this->alchemyApiKey, $this->httpClient, $this->logger);
    }

    public function getPriceBySymbol(string $symbol) : Coin
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

            $coin = (new Coin())
                ->setSymbol($data['symbol'])
                ->setName($data['symbol'])
                ->setPrice(floatval(current($data['prices'])['value']));

//            todo contractAddress
//            $this->coinRepository->save($coin);
        } else {
            $this->logger->error('alchemy [priceService] error', ['contnt' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new \HttpException('Не удалось получить данные из стороннего ресурса');
        }

        return $coin;
    }


}
