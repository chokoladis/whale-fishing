<?php

namespace App\Repository\User;

use App\Entity\User\User;
use App\Entity\UserVerification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserVerification>
 */
class UserVerificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVerification::class);
    }

    public function getRowsByUserForDay(User $user): array
    {
        return $this->createQueryBuilder('uv')
            ->andWhere('uv.user = :user')
            ->andWhere('uv.createdAt > :lastDay')
            ->setParameter('user', $user)
            ->setParameter('lastDay', (new \DateTime('now'))->modify('-1day'))
            ->getQuery()
            ->getResult();
    }

    public function save(UserVerification $userVerification, ?EntityManagerInterface $manager = null): void
    {
        $manager = $manager ?: $this->getEntityManager();
        if (!$userVerification->getId())
            $manager->persist($userVerification);

        $manager->flush();
    }
}
