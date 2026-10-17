<?php

namespace App\Repository\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User\TelegramLinkRequest;

/**
 * @extends ServiceEntityRepository<TelegramLinkRequest>
 */
class TelegramLinkRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramLinkRequest::class);
    }

    public function findNoExpiredByChatId(int $chatId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.chatId = :chatId')
            ->andWhere('t.expiredAt > :now')
            ->setParameter('chatId', $chatId)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRowWithUserByTelegramChatId(int $chatId) : ?TelegramLinkRequest
    {
        // todo create index?
        return $this->createQueryBuilder('t')
            ->andWhere('t.chatId = :chatId')
            ->andWhere('t.user IS NOT NULL')
            ->setParameter('chatId', $chatId)
            ->getQuery()
            ->getOneOrNullResult();
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
