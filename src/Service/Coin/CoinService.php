<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\DTO\Http\Response\TransactionDTO;
use App\Entity\CoinContract;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Helper\StrHelper;
use App\Messages\GetCoinBySymbolMessage;
use App\Repository\CoinContractRepository;
use App\Repository\CoinRepository;
use App\Resource\Coin\CoinResource;
use App\Service\External\Alchemy\TransactionService;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CoinService
{
    public function __construct(
        private CoinRepository $coinRepository,
        private CoinContractRepository $coinContractRepository,
        private CoinResource $coinResource,
        private TransactionService $transactionService,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus
    )
    {
    }

    public function getCoins(?ListRequest $request): PageDTO
    {
        return $this->coinRepository->getList($request);
    }

    /**
     * @param string $symbol
     * @return array<string, mixed>|null
     * @throws InvalidCoinSymbolException
     */
    public function getCoin(string $symbol): ?array
    {
        $symbol = trim($symbol);
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $coin = $this->coinRepository->findOneBy(['symbol' => $symbol]);
        if (empty($coin)) {
            $this->messageBus->dispatch(new GetCoinBySymbolMessage($symbol));
            throw new EntityNotFoundException('Данной монеты нет в базе');
        }

        if (!$coin->getPrice()) {
            $this->messageBus->dispatch(new GetCoinBySymbolMessage($symbol));
        }

        return $this->coinResource->detail($coin);
    }

    /**
     * @param TransactionDTO $transactionDTO
     * @return CoinContract
     * @throws \App\Exception\External\IntegrationException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createOrFindByTransaction(TransactionDTO $transactionDTO)
    {
        $this->logger->debug('createOrFindByTransaction');
        // todo mb find by network too ?
        if ($coin = $this->coinContractRepository->findByAddressAndNetwork(
            $transactionDTO->contractAddress,
            $transactionDTO->network
        )){
            return $coin;// todo ?
        }

        if ($transfer = $this->transactionService->getAssetTransferByTransactionDTO($transactionDTO)){

//            $this->logger->debug('transaction dto and transfer', [$transactionDTO, $transfer]);
//            [
//                {"App\\DTO\\Http\\Response\\TransactionDTO":
//                    {"blockNumber":"0x185465d","hash":"0x6e00465323d05ded34e4cd8d2a9f0113d4946f80bc37832057c1d6205006e33a","from":"0x9c169a9cff6fe58560009e3a69bbbfce666d5674","to":"0xa9d1e08c7793af67e9d92fe308d5697fb81d3e43","contractAddress":"0xdac17f958d2ee523a2206206994597c13d831ec7","amountRaw":"184600000","network":"ETH"}
//                },
//                {"blockNum":"0x185465d","uniqueId":"0x6e00465323d05ded34e4cd8d2a9f0113d4946f80bc37832057c1d6205006e33a:log:45","hash":"0x6e00465323d05ded34e4cd8d2a9f0113d4946f80bc37832057c1d6205006e33a","from":"0x9c169a9cff6fe58560009e3a69bbbfce666d5674","to":"0xa9d1e08c7793af67e9d92fe308d5697fb81d3e43","value":184.6,"erc721TokenId":null,"erc1155Metadata":[],"tokenId":null,"asset":"USDT","category":"erc20","rawContract":{"value":"0xb00c5c0","address":"0xdac17f958d2ee523a2206206994597c13d831ec7","decimal":"0x6"},"metadata":{"blockTimestamp":"2026-07-11T19:38:59.000Z"}}
//            ]

            try {
                return $this->coinContractRepository->saveByDTO(
                    new CoinShortDTO(
                        $transfer['asset'],
                        $transactionDTO->contractAddress,
                        $transactionDTO->network,
                        (int)StrHelper::bchexdec($transfer['rawContract']['decimal'])
                    )
                );
            } catch (\Throwable $e) {
                $this->logger->error('error in coinService' , [$e->getMessage(), $e->getFile(), $e->getLine()]);
            }
        }

        return null;
    }
}
