<?php

declare(strict_types=1);

namespace App\Service\Alchemy;

use App\Exception\Coin\InvalidCoinSymbolException;
use Doctrine\ORM\Query\Expr\Base;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class WalletService extends BaseService
{
    protected const BASE_URL = 'https://eth-mainnet.g.alchemy.com';

    const ITEMS_PER_PAGE = 10;

    public function __construct(
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
