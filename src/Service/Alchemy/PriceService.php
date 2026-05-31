<?php

namespace App\Service\Alchemy;

use App\Entity\Coin;
use App\Exception\Coin\InvalidCoinSymbolException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class PriceService extends BaseService
{

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
            // current($data['prices'])['currency']

            $coin = (new Coin())->setSymbol($data['symbol'])
                ->setName($data['symbol'])
                ->setPrice(floatval(current($data['prices'])['value']));

            $this->coinRepository->saveFromAlchemy($coin);
        } else {
            $this->logger->error('alchemy [priceService] error', ['contnt' => $response->getContent(), 'status' => $response->getStatusCode()]);

            throw new \HttpException('Не удалось получить данные из стороннего ресурса');
        }

        return $coin;
    }


}
