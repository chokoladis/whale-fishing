<?php

namespace App\Service\External\Alchemy;

use App\Config\External\AlchemyConfig;
use App\DTO\Http\Response\TransactionDTO;
use App\Exception\External\IntegrationException;
use App\Repository\CoinRepository;
use App\Repository\TransactionRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransactionService extends ClientService
{
    const string BASE_URL = AlchemyConfig::ETH_MAINNET_DOMAIN;

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
        protected TransactionRepository $transactionRepository,
    )
    {
        parent::__construct($this->alchemyApiKey, $this->httpClient, $this->logger);
    }


    /**
     * @param TransactionDTO $transactionDTO
     * @return mixed
     * @throws IntegrationException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getAssetTransferByTransactionDTO(TransactionDTO $transactionDTO) : mixed
    {
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
                            'category' => ['external', 'erc20'],
                            'excludeZeroValue' => true,
                            'maxCount' => '0x1',
                        ]
                    ]
                ]
                ]);
        } catch (\Throwable $e) {
            $this->logger->error('error handle in service', [$e->getMessage(), $e->getFile(), $e->getLine(), $this->alchemyApiKey]);
            return null;
        }

        $data = $response->toArray();

        $transfers = $data['result']['transfers'] ?? [];

        $targetContract = $transactionDTO->contractAddress ? strtolower($transactionDTO->contractAddress) : null;
        $targetHash = strtolower($transactionDTO->hash);

        if (!empty($transfers)) {
            foreach ($transfers as $transfer) {
                $transferHash = strtolower($transfer['hash'] ?? '');

                $transferContract = isset($transfer['rawContract']['address'])
                    ? strtolower($transfer['rawContract']['address'])
                    : null;

                if ($transferHash === $targetHash && $transferContract === $targetContract) {
                    if (isset($transfer['rawContract']['address'])) {
                        $transfer['rawContract']['address'] = $transferContract;
                    }

                    return $transfer;
                }
            }
        }

        return null;
    }
}
