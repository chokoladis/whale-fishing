<?php

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findByTopHoldersBySymbol(string $symbol, float $value)
    {
        return $this->createQueryBuilder('w')
            ->addSelect('w')
            ->addSelect('(wc.balance * wc.avgPrice) as HIDDEN totalValue')
            ->join('w.walletCoins', 'wc')
            ->join('wc.coin', 'c')
            ->andWhere('wc.balance >= :val')
            ->andWhere('c.symbol = :coin')
            ->setParameter('coin', $symbol)
            ->setParameter('val', $value)

            ->orderBy('totalValue', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOrCreateByAddress(string $address): ?Wallet
    {
        if ($wallet = $this->findOneBy(['address' => $address])) {
            return $wallet;
        }

        return $this->save($address);
    }

    public function save(string $address): Wallet
    {
        $wallet = new Wallet();
        $wallet->setAddress($address);

        $this->getEntityManager()->persist($wallet);
        $this->getEntityManager()->flush();

        return $wallet;
    }
}
