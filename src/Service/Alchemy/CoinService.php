<?php

namespace App\Service\Alchemy;

use App\Config\External\AlchemyConfig;
use App\Service\Alchemy\BaseService;
use Symfony\Component\HttpClient\HttpClient;

class CoinService extends BaseService
{
    const BASE_URL = AlchemyConfig::ETH_MAINNET_DOMAIN;
    public function getTokenDataByContractAddress(string $contractAddress): ?array
    {
        $httpRequest = HttpClient::create();

//        $response = $httpRequest->request('POST', 'https://'.self::BASE_URL.'/v2/' . $this->alchemyApiKey, [
//            'json' => [
//                'id' => 1,
//                'jsonrpc' => '2.0',
//                'method' => 'alchemy_getAssetTransfers',
//                'params' => [
//                    [
//                        'fromBlock' => $blockNumber,
//                        'toBlock' => $blockNumber,
//                        'fromAddress' => $contractAddress,
//                        // 'external' — это переводы нативного ETH
//                        // 'erc20' — это чистые переводы токенов (без DEX-мусора)
//                        'category' => ['external', 'erc20'],
//                        'excludeZeroValue' => true,
//                        'maxCount' => '0x64'        // Ограничим 100 результатами для теста
//                    ]
//                ]
//            ]
//        ]);
//
//        $data = $response->toArray();
//        $transfers = $data['result']['transfers'] ?? [];

        dd(1);
        foreach ($transfers as $transfer) {
            $symbol = $transfer['asset']; // Например: "ETH", "USDT"

            // Для нативного ETH адрес контракта будет null в API Alchemy
            // Для ERC-20 токенов там будет валидный адрес (0x...)
            $contractAddress = $transfer['rawContract']['address'] ?? 'native';

            // Ищем монету в базе по связке Сеть + Контракт
            $coin = $this->coinRepository->findOneBy([
                'network' => 'eth-mainnet',
                'contractAddress' => $contractAddress
            ]);

            // Если такой монеты еще нет в базе — создаем её
            if (!$coin) {
                $coin = new Coin();
                $coin->setNetwork('eth-mainnet');
                $coin->setContractAddress($contractAddress);
                $coin->setSymbol($symbol);
                $coin->setName($symbol); // Имя можно временно поставить как символ
                $coin->setPrice(0.0);    // Цену обновит отдельный крон-сервис по символу

                $this->em->persist($coin);
                // Чтобы не частить flush'ами, можно вынести его за пределы цикла
            }
        }

        $this->em->flush();
        $this->em->clear();

        return $data['result'] ?? null;
    }
}
