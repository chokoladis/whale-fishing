<?php

declare(strict_types=1);

namespace App\Service\External\Alchemy;

use App\Config\External\AlchemyConfig;
use App\Exception\Coin\InvalidCoinSymbolException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class WalletService extends ClientService
{
    protected const string BASE_URL = 'https://'.AlchemyConfig::ETH_MAINNET_DOMAIN;

    const ITEMS_PER_PAGE = 10;

    public function getTopHolders(string $coinName)
    {
        $httpRequest = HttpClient::create();

        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $response = $httpRequest->request('GET',
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

        $fileLog = $_SERVER["DOCUMENT_ROOT"].'/log_debug.php';

        $log = date('Y-m-d H:i:s') . ' wallet response - '.print_r( [$response->getStatusCode(), $response->getContent()], true);
        file_put_contents($fileLog, $log . PHP_EOL, FILE_APPEND);

        if ($response->getStatusCode() === Response::HTTP_OK) {

        }
    }
}
