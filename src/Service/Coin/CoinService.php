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
        // todo mb find by network too ?
        if ($coin = $this->coinContractRepository->findByAddressAndNetwork(
            $transactionDTO->contractAddress,
            $transactionDTO->network
        )){
            return $coin;// todo ?
        }

        if ($transfer = $this->transactionService->getAssetTransferByTransactionDTO($transactionDTO)){
            return $this->coinContractRepository->saveByDTO(
                new CoinShortDTO(
                    $transfer['asset'],
                    $transactionDTO->contractAddress,
                    $transactionDTO->network,
                    (int)StrHelper::bchexdec($transfer['rawContract']['decimal'])
                )
            );
        }

        $this->logger->debug('не получилось достать coin из транзакции' , [$transactionDTO]);

        return null;
    }
}
