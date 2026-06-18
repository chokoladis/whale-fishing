<?php

namespace App\Service\Wallet;

use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Transaction;
use App\Exception\External\IntegrationException;
use App\Repository\CoinRepository;
use App\Repository\TransactionRepository;
use App\Resource\TransactionResource;
use Psr\Log\LoggerInterface;

class TransactionService
{
    public function __construct(
        protected LoggerInterface $logger,
        protected CoinRepository $coinRepository,
        protected TransactionRepository $transactionRepository,
        protected TransactionResource $transactionResource,
    )
    {
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
    public function getTopHourly()
    {
        $datetime = new \DateTime();
        $datetime->modify('-1 hour');

        return array_map(fn(Transaction $transaction) => $this->transactionResource->getShortData($transaction),
            $this->transactionRepository->getTopTransactionsByTime($datetime));
    }

    public function getTopDaily() : mixed
    {
        $datetime = new \DateTime();
        $datetime->modify('-24 hours');

        return array_map(fn(Transaction $transaction) => $this->transactionResource->getShortData($transaction),
            $this->transactionRepository->getTopTransactionsByTime($datetime));
    }

    public function getList(?ListRequest $listRequest)
    {
        return $this->transactionRepository->getList($listRequest);
    }
}
