<?php

declare(strict_types=1);

namespace App\Service\External\Alchemy;

use App\Config\External\AlchemyConfig;
use App\Exception\Coin\InvalidCoinSymbolException;
use Symfony\Component\HttpFoundation\Response;

class WalletService extends ClientService
{
    protected const string BASE_URL = 'https://'.AlchemyConfig::ETH_MAINNET_DOMAIN;

    const ITEMS_PER_PAGE = 10;

    public function getTopHolders(string $symbol) : void
    {
        $symbol = trim($symbol);
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $response = $this->httpClient->request('GET',
            sprintf('%s/v2/%s', self::BASE_URL, $this->alchemyApiKey),
            [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method'  => 'alchemy_getTokenBalances',
                    'params'  => [
                        $symbol,
                        'erc20',
                        ['maxCount' => self::ITEMS_PER_PAGE]
                    ],
                    'id' => 1,
                ]
            ]
        );

        $this->logger->debug(' wallet response - ', [$response->getStatusCode(), $response->getContent()]);

        if ($response->getStatusCode() === Response::HTTP_OK) {

        }
    }
}
