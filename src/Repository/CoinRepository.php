<?php

namespace App\Repository;

use App\DTO\CoinDTO;
use App\Entity\Coin;
use App\Request\Coin\ListRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coin>
 */
class CoinRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coin::class);
    }

    //    /**
    //     * @return Coin[] Returns an array of Coin objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult() //->getOneOrNullResult()
    //        ;
    //    }


    public function save(CoinDTO $coinDTO): Coin
    {
        $coin = new Coin();
        $coin->setNetwork($coinDTO->network);
        $coin->setContractAddress($coinDTO->contractAddress);
        $coin->setSymbol($coinDTO->symbol);
        $coin->setName($coinDTO->symbol);
        $coin->setDecimal($coinDTO->decimal);
        $coin->setPrice(0.0);

        $this->getEntityManager()->persist($coin);
        $this->getEntityManager()->flush();

        return $coin;
    }

    public function getList(?ListRequest $listRequest): Paginator
    {
        $query = $this->createQueryBuilder('coin')
            ->orderBy('coin.price', 'DESC');

        return $this->paginate($query, $listRequest?->page, $listRequest?->perPage);
    }

    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    public function findByContractAddress(string $address): ?Coin
    {
        return $this->findOneBy([
            'contractAddress' => strtolower($address)
        ]);
    }
}
