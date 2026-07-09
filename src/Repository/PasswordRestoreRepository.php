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

    /**
     * @param int $userId
     * @return array<int, PasswordRestore>
     * @throws \DateMalformedStringException
     */
    public function getRowsByUserIdForDay(int $userId) : array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userId = :id')
            ->andWhere('p.expiredAt > :now')
            ->setParameter('id', $userId)
            ->setParameter('now', new \DateTime('now')->modify('-1day'))
            ->getQuery()
            ->getResult();
    }

    public function getActiveByToken(string $token, int $userId) : ?PasswordRestore
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.token = :token')
            ->andWhere('p.userId = :userId')
            ->andWhere('p.expiredAt > :now')
            ->setParameter('token', $token)
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return PasswordRestore|null
     * @description only for test
     */
    public function getNotActiveTokenByEmail(string $email) : ?PasswordRestore
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->andWhere('u.email = :email')
            ->andWhere('p.expiredAt < :now')
            ->setParameter('email', $email)
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastByUserEmail(string $email) : ?PasswordRestore
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->andWhere('u.email = :email')
            ->andWhere('p.expiredAt < :now')
            ->setParameter('email', $email)
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PasswordRestore $passwordRestore) : void
    {
        $this->getEntityManager()->persist($passwordRestore);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<int, int>
     * @throws \DateMalformedStringException
     * @description for cron task
     */
    public function getOldRows() : array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('p.id')
            ->andWhere(':twoDayAgo > p.createdAt')
            ->setParameter('twoDayAgo', new \DateTime('now')->modify('-2day'))
            ->getQuery()
            ->getResult();
    }

    public function deleteById(int $id) : mixed
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array<int, int> $ids
     * @return mixed
     */
    public function deleteByIds(array $ids) : mixed
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
