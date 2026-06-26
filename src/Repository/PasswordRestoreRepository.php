<?php

namespace App\Repository;

use App\Entity\PasswordRestore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordRestore>
 */
class PasswordRestoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordRestore::class);
    }

    public function findCountByUserId(int $userId) : int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p) as count')
            ->andWhere('p.userId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getActiveByToken(string $token, int $userId) : ?PasswordRestore
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.token = :token')
            ->andWhere('p.userId = :userId')
            ->andWhere('p.expiredAt < :now')
            ->setParameter('token', $token)
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PasswordRestore $passwordRestore)
    {
        $this->getEntityManager()->persist($passwordRestore);
        $this->getEntityManager()->flush();
    }
}
