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

//    /**
//     * @return Wallet[] Returns an array of Wallet objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findByMinValue(string $symbol, float $value)
    {
        return $this->createQueryBuilder('w')
            ->addSelect('(w.qty * w.priceAvg) as HIDDEN totalValue')
            ->join('w.coin', 'c')
            ->andWhere('(w.qty * w.priceAvg) >= :val')
            ->andWhere('c.name = :coin')
            ->setParameter('coin', $symbol)
            ->setParameter('val', $value)

            ->orderBy('totalValue', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
