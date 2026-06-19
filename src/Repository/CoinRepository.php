<?php

namespace App\Repository;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\Entity\Coin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @extends ServiceEntityRepository<Coin>
 */
class CoinRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private ContainerBagInterface $params,
    )
    {
        parent::__construct($registry, Coin::class);
    }

    public function saveByDTO(CoinShortDTO $coinDTO): Coin
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

    public function updatePrice(Coin $coin, float $price): Coin
    {
        $coin->setPrice($price);

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

    public function paginate(QueryBuilder $dql, ?int $page = 1, ?int $limit = null)
    {
        $limit = $limit ?? $this->params->get('listing.limit');

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
