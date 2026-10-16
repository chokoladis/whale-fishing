<?php

namespace App\Repository;

use App\Entity\TelegramLinkRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramLinkRequest>
 */
class TelegramLinkRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramLinkRequest::class);
    }

    /**
     * @return TelegramLinkRequest[] Returns an array of TelegramAuth objects
     */
    public function findNoExpiredByIpAddress(string $ip): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.ipAddress = :ip')
            ->andWhere('t.expiredAt > :now')
            ->setParameter('ip', $ip)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?TelegramAuth
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function save(TelegramLinkRequest $telegramLinkRequest)
    {
        if (!$telegramLinkRequest->getId())
            $this->getEntityManager()->persist($telegramLinkRequest);

        $this->getEntityManager()->flush();
    }
}
