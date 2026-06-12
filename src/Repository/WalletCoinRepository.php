<?php

namespace App\Repository;

use App\Entity\Wallet;
use App\Entity\WalletCoin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletCoin>
 */
class WalletCoinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletCoin::class);
    }

    public function save(WalletCoin $walletCoin): void
    {
        $this->getEntityManager()->persist($walletCoin);
        $this->getEntityManager()->flush();
    }
}
