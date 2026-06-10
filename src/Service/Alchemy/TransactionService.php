<?php

namespace App\Service\Alchemy;

use App\Config\External\AlchemyConfig;
use App\DTO\Http\Response\TransactionDTO;
use App\Exception\Alchemy\IntegrationException;
use App\Service\Alchemy\AlchemyClientService;
use Symfony\Component\HttpClient\HttpClient;

class TransactionService extends AlchemyClientService
{
    const BASE_URL = AlchemyConfig::ETH_MAINNET_DOMAIN;

    /**
     * @param TransactionDTO $transactionDTO
     * @return void
     * @throws IntegrationException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getAssetTransferByTransactionDTO(TransactionDTO $transactionDTO) : ?array
    {
        $this->logger->info("Get CoinInfoByTransaction dto", [$transactionDTO]);

        try {
            $response = $this->httpClient->request('POST',
                sprintf('https://%s/v2/%s',self::BASE_URL, $this->alchemyApiKey),
                [ 'json' => [
                    'id' => 1,
                    'jsonrpc' => '2.0',
                    'method' => 'alchemy_getAssetTransfers',
                    'params' => [
                        [
                            'fromBlock' => $transactionDTO->blockNumber,
                            'toBlock' => $transactionDTO->blockNumber,
                            'fromAddress' => $transactionDTO->from,
                            'contractAddress' => [$transactionDTO->contractAddress],
                            // 'external' — это переводы нативного ETH ,
                            // 'erc20' — это чистые переводы токенов (без DEX-мусора)
                            'category' => ['external', 'erc20'],
                            'excludeZeroValue' => true,
                            'maxCount' => '0x64', // Ограничим 100 результатами для теста,
                        ]
                    ]
                ]
                ]);
        } catch (\Throwable $e) {
            $this->logger->error('error handle in service', [$e->getMessage(), $e->getFile(), $e->getLine(), $this->alchemyApiKey]);
            dd();
        }

        $data = $response->toArray();

        $this->logger->debug('$response transaction data', ['data' => $data, 'type' => gettype($data)]);

//              {"blockNum":"0x181e268","value":1301.279157,"erc721TokenId":null,"erc1155Metadata":[],"tokenId":null,"asset":"USDT","category":"erc20","rawContract":{"value":"0x4d8ff1b5","address":"0xdac17f958d2ee523a2206206994597c13d831ec7","decimal":"0x6"},"metadata":{"blockTimestamp":"2026-06-10T20:05:23.000Z"}}]}},"type":"array"} []
        $transfers = $data['result']['transfers'] ?? [];

        if (!empty($transfers)) {
            $transfer = current($transfers);
        }

        return $transfer ?? null;
    }
}
