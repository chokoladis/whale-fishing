<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Repository\CoinRepository;
use App\Request\Coin\ListRequest;
use App\Service\Alchemy\TransactionService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoinService
{
    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        private string $alchemyApiKey,
        private CoinRepository $coinRepository,
        private TransactionService $transactionService,
    )
    {
    }

    public function getCoins(?ListRequest $request): Paginator
    {
//        handle error
        return $this->coinRepository->getList($request);
    }

    /**
     * @param TransactionDTO $transactionDTO
     * @return Coin
     * @throws \App\Exception\Alchemy\IntegrationException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createOrFindByTransaction(TransactionDTO $transactionDTO): ?Coin
    {
//        todo mb find by network too ?
        if ($coin = $this->coinRepository->findByContractAddress($transactionDTO->contractAddress)){
            return $coin;
        }

        if ($transfer = $this->transactionService->getAssetTransferByTransactionDTO($transactionDTO)){

//            todo save transaction

//            $transfer['value']; // for transaction
//
//            $transfer['category']; //"category":"erc20"
//            $transfer['rawContract']['decimal']; //","decimal":"0x6"


//            todo fix save coin
            $coin = new Coin();
            $coin->setNetwork('eth-mainnet');
            $coin->setContractAddress($transactionDTO->contractAddress);
            $coin->setSymbol($transfer['asset']);
            $coin->setName($transfer['asset']);
            $coin->setPrice(0.0);    // Цену обновит отдельный крон-сервис по символу

            $this->coinRepository->saveFromAlchemy($coin);

            return $coin;
        }

        return null; // mb throw error
    }
}
