<?php

namespace App\Repository;

use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Enum\Coin\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $manager,
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Wallet $wallet, TransactionDTO $transaction, Coin $coin, TransactionType $type): void
    {
        $amount = bcdiv(
            $transaction->amountRaw,
            bcpow('10', (string)$coin->getDecimal()),
            $coin->getDecimal()
        );

        $newTransaction = new Transaction();
        $newTransaction->setBlockNumber($transaction->blockNumber);
        $newTransaction->setHash($transaction->hash);
        $newTransaction->setFrom($transaction->from);
        $newTransaction->setTo($transaction->to);
        $newTransaction->setType($type);
        $newTransaction->setAmount($amount);

        $newTransaction->setWallet($wallet);
        $newTransaction->setCoin($coin);

        $this->manager->persist($newTransaction);
        $this->manager->flush();
    }
}
