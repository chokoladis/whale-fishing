<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\DTO\Http\Response\TransactionDTO;
use App\Entity\CoinContract;
use App\Entity\CoinDetail;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Helper\StrHelper;
use App\Messages\LoadCoinBySymbolMessage;
use App\Repository\CoinContractRepository;
use App\Repository\CoinDetailRepository;
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
        private CoinDetailRepository $coinDetailRepository,
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
        if (empty($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        $coin = $this->coinRepository->findOneBy(['symbol' => $symbol]);
        if (empty($coin)) {
            $this->messageBus->dispatch(new LoadCoinBySymbolMessage($symbol));
            throw new EntityNotFoundException('Данной монеты нет в базе');
        }

        if (empty($coin->getAvgPrice())) {
            $this->messageBus->dispatch(new LoadCoinBySymbolMessage($symbol));
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

    public function fullUpdateCoin(CoinContract $coinContract , \App\DTO\Http\Response\Coin\CoinDetailResponse $coinDetailResponse): void
    {
        $this->logger->info('full update coin', ['response' => $coinDetailResponse]);
        //        todo in one transaction
        $this->coinContractRepository->updatePrice($coinContract,  $coinDetailResponse->price);

        $coin = $coinContract->getCoin();
        if ($coin->getName() !== $coinDetailResponse->name) {
            $coin->setName($coinDetailResponse->name);
        }
        $this->coinRepository->updatePrice($coin, $coinDetailResponse->price);

        $stats = $coinDetailResponse->statistics;

        $coinDetail = new CoinDetail();
        $coinDetail->setCoin($coin);
        $coinDetail->setMarketCap($stats->marketCap);
        $coinDetail->setVolume($stats->volume);
        $coinDetail->setLiquidity($stats->liquidity);
        $coinDetail->setTotalSupply($stats->totalSupply);
        $coinDetail->setCirculationSupply($stats->circulationSupply);

        if ($stats->maxSupply)
            $coinDetail->setMaxSupply($stats->maxSupply);

        $this->coinDetailRepository->save($coinDetail);
    }
}
