<?php

namespace App\Repository\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User\TelegramRequest;

/**
 * @extends ServiceEntityRepository<TelegramRequest>
 */
class TelegramRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramRequest::class);
    }

    public function findByChatId(int $chatId): ?TelegramRequest
    {
        //todo create index chatid
        return $this->createQueryBuilder('t')
            ->andWhere('t.chatId = :chatId')
            ->setParameter('chatId', $chatId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findRowWithUserByTelegramChatId(int $chatId) : ?TelegramRequest
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

    public function save(TelegramRequest $telegramLinkRequest)
    {
        if (!$telegramLinkRequest->getId())
            $this->getEntityManager()->persist($telegramLinkRequest);

        $this->getEntityManager()->flush();
    }
}
