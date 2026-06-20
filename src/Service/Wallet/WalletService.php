<?php

declare(strict_types=1);

namespace App\Service\Wallet;

use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Entity\Wallet;
use App\Entity\WalletCoin;
use App\Enum\Coin\TransactionType;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Repository\TransactionRepository;
use App\Repository\WalletCoinRepository;
use App\Repository\WalletRepository;
use App\Resource\WalletResource;

class WalletService
{
    const int ITEMS_PER_PAGE = 10;
    const float MIN_VALUE_TOP_HOLDER = 100000;

    public function __construct(
        private WalletRepository                            $walletRepository,
        private WalletCoinRepository                        $walletCoinRepository,
        private TransactionRepository                       $transactionRepository,
        private WalletResource $walletResource,
        private \App\Service\External\Alchemy\WalletService $alchemyService,
    )
    {
    }

    public function getTopHolders(string $coinName)
    {
        $symbol = strtoupper(trim($coinName));
        if (!mb_strlen($symbol)) {
            throw new InvalidCoinSymbolException('Symbol cannot be empty.');
        }

//        todo paginator
        $wallets = $this->walletRepository->findByTopHoldersBySymbol($symbol, self::MIN_VALUE_TOP_HOLDER);
        if (empty($wallets)) {
//            todo
//            $wallets = $this->alchemyService->getTopHolders($symbol);
        }

        return array_map(fn (Wallet $wallet) => $this->walletResource->detail($wallet), $wallets);
    }

    public function addTransactions(TransactionDTO $transaction, Coin $coin)
    {
        $walletFrom = $this->walletRepository->findOrCreateByAddress($transaction->from);

        $this->updateWalletCoin(
            $walletFrom,
            $coin,
            $transaction->amountRaw,
            TransactionType::OUT
        );
        $this->transactionRepository->save($walletFrom, $transaction, $coin, TransactionType::OUT);

        //

        $walletTo = $this->walletRepository->findOrCreateByAddress($transaction->to);
        $this->updateWalletCoin(
            $walletTo,
            $coin,
            $transaction->amountRaw,
            TransactionType::IN
        );
        $this->transactionRepository->save($walletTo, $transaction, $coin, TransactionType::IN);
    }

    public function updateWalletCoin(Wallet $wallet, Coin $coin, string $amount, TransactionType $type)
    {
        $walletCoin = $this->walletCoinRepository->findOneBy([
            'wallet' => $wallet,
            'coin'   => $coin,
        ]);

        if (!$walletCoin) {
            $walletCoin = new WalletCoin();
            $walletCoin->setWallet($wallet);
            $walletCoin->setCoin($coin);
        }

        $currentBalance = $walletCoin->getBalance();

        $newBalance = $type === TransactionType::IN
            ? bcadd($currentBalance, $amount)
            : bcsub($currentBalance, $amount);

        $walletCoin->setBalance($newBalance);

        $this->walletCoinRepository->save($walletCoin);
    }

    public function getDetail(string $address)
    {
        $wallet = $this->walletRepository->findOneBy([
            'address' => $address,
        ]);

        return $this->walletResource->detail($wallet);
    }
}
