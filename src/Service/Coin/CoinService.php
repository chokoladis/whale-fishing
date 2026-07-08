<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Helper\StrHelper;
use App\Repository\CoinRepository;
use App\Resource\CoinResource;
use App\Service\External\Alchemy\TransactionService;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoinService
{
    public function __construct(
        private CoinRepository $coinRepository,
        private CoinResource $coinResource,
        private TransactionService $transactionService,
        private LoggerInterface $logger,
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
            throw new EntityNotFoundException('Данной монеты нет в базе');
//            todo cron for this
//            private PriceService $priceService,
//            || !$coin->getPrice() || $coin->getPrice() === 0.0
//            $coin = $this->priceService->getPriceBySymbol($symbol);
        }

        return $this->coinResource->detail($coin);
    }

    /**
     * @param TransactionDTO $transactionDTO
     * @return Coin
     * @throws \App\Exception\External\IntegrationException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createOrFindByTransaction(TransactionDTO $transactionDTO): ?Coin
    {
        // todo mb find by network too ?
        if ($coin = $this->coinRepository->findByContractAddress($transactionDTO->contractAddress)){
            return $coin;
        }

        if ($transfer = $this->transactionService->getAssetTransferByTransactionDTO($transactionDTO)){

            $this->logger->debug('transaction dto and transfer', [$transactionDTO, $transfer]);

            try {
                return $this->coinRepository->saveByDTO(
                    new CoinShortDTO(
                        $transfer['category'] ?? 'native',
                        $transactionDTO->contractAddress,
                        $transfer['asset'],
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
