<?php

namespace App\Repository;

use App\Entity\CoinDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoinDetail>
 */
class CoinDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoinDetail::class);
    }

    public function save(CoinDetail $coinDetail)
    {
        $this->getEntityManager()->persist($coinDetail);
        $this->getEntityManager()->flush();
    }
}
