<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\Exception\Coin\InvalidCoinSymbolException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class WalletService
{
    const ITEMS_PER_PAGE = 10;

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        private string $alchemyApiKey
    )
    {
    }

    public function getTopHolders(string $coinName)
    {
        $httpRequest = HttpClient::create();

        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $response = $httpRequest->request('GET', "https://eth-mainnet.g.alchemy.com/v2/{$this->alchemyApiKey}", [
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
        ]);

        $fileLog = $_SERVER["DOCUMENT_ROOT"].'/log_debug.php';

        $log = date('Y-m-d H:i:s') . ' $symbol + $response - '.print_r( [$symbol, $response], true);
        file_put_contents($fileLog, $log . PHP_EOL, FILE_APPEND);


        $log = date('Y-m-d H:i:s') . ' detail response - '.print_r( [$response->getStatusCode(), $response->getContent()], true);
        file_put_contents($fileLog, $log . PHP_EOL, FILE_APPEND);

        if ($response->getStatusCode() === Response::HTTP_OK) {

        }
    }
}
