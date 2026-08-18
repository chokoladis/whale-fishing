<?php

declare(strict_types=1);

namespace App\Service\Wallet;

use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Entity\Wallet;
use App\Entity\WalletCoin;
use App\Enum\Coin\TransactionType;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Helper\StrHelper;
use App\Messages\SetWalletCoinWithActualPriceMessage;
use App\Repository\TransactionRepository;
use App\Repository\WalletCoinRepository;
use App\Repository\WalletRepository;
use App\Resource\WalletResource;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

class WalletService
{
    const float MIN_VALUE_TOP_HOLDER = 100000;

    public function __construct(
        private WalletRepository      $walletRepository,
        private WalletCoinRepository  $walletCoinRepository,
        private TransactionRepository $transactionRepository,
        private WalletResource        $walletResource,
        private MessageBusInterface $messageBus,
        #[Autowire(service: 'monolog.logger.services')]
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function getTopHolders(string $coinName): mixed
    {
        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

        //        todo paginator
        $wallets = $this->walletRepository->findByTopHoldersBySymbol($symbol, self::MIN_VALUE_TOP_HOLDER);
        if (empty($wallets)) {
//            $wallets = $this->alchemyService->getTopHolders($symbol);
        }

        return array_map(fn(Wallet $wallet) => $this->walletResource->detail($wallet), $wallets);
    }

    public function addTransactions(TransactionDTO $transaction, CoinContract $coinContract): void
    {
        // todo протестировать и оптимизировать
        $this->entityManager->beginTransaction();

        try {
            $walletFrom = $this->walletRepository->findOrCreateByAddress($transaction->from);

            $this->updateWalletCoin(
                $walletFrom,
                $coinContract->getCoin(), //todo optimize
                $transaction->amountRaw,
                TransactionType::OUT
            );

            $this->transactionRepository->save($walletFrom, $transaction, $coinContract, TransactionType::OUT);

            $walletTo = $this->walletRepository->findOrCreateByAddress($transaction->to);
            $this->updateWalletCoin(
                $walletTo,
                $coinContract->getCoin(),
                $transaction->amountRaw,
                TransactionType::IN
            );
            $this->transactionRepository->save($walletTo, $transaction, $coinContract, TransactionType::IN);
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->logger->error('ошибка при добавлении транзакций', [$e->getMessage(), $e->getLine(), $e->getFile()]);
            $this->entityManager->rollBack();
            throw $e;
        }
    }

    public function updateWalletCoin(Wallet $wallet, Coin $coin, string $amount, TransactionType $type): void
    {
        $walletCoin = $this->walletCoinRepository->findOneBy([
            'wallet' => $wallet,
            'coin' => $coin,
        ]);

        if (!$walletCoin) {
            $walletCoin = new WalletCoin();
            $walletCoin->setWallet($wallet);
            $walletCoin->setCoin($coin);
            $currentBalance = '0';
        } else {
            $currentBalance = $walletCoin->getBalance();
        }

        //todo optimize
        $contract = $coin->getCoinContract()->current();

        $newBalance = $type === TransactionType::IN
            ? bcadd($currentBalance, $amount)
            : bcsub($currentBalance, $amount);

        $amount = StrHelper::trimZeros(bcdiv($newBalance, bcpow('10', (string)$contract->getDecimal()), $contract->getDecimal()));

        $walletCoin->setBalance($amount);
        $this->walletCoinRepository->save($walletCoin);

        // todo or update by cron?
        $this->messageBus->dispatch(new SetWalletCoinWithActualPriceMessage($walletCoin->getId(), new \DateTimeImmutable(), $type, $amount));
    }

    public function getDetail(string $address): mixed
    {
        $wallet = $this->walletRepository->findOneBy([
            'address' => $address,
        ]);

        return $this->walletResource->detail($wallet);
    }
}
