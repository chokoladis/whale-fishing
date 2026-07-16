<?php

namespace App\Repository;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\Entity\Coin;
use App\Resource\Coin\CoinResource;
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
        private CoinResource $coinResource
    )
    {
        parent::__construct($registry, Coin::class);
    }

    public function saveByDTO(CoinShortDTO $coinDTO): Coin
    {
        $coin = new Coin();
        $coin->setSymbol($coinDTO->symbol);
        $coin->setName($coinDTO->symbol);

        $this->getEntityManager()->persist($coin);
        $this->getEntityManager()->flush();

        return $coin;
    }

    public function updatePrice(Coin $coin, string $price): Coin
    {
        $coin->setAvgPrice($price);

        $this->save($coin);

        return $coin;
    }

    public function save(Coin $coin)
    {
        $this->getEntityManager()->persist($coin);
        $this->getEntityManager()->flush();
    }

    public function getList(?ListRequest $listRequest): PageDTO
    {
        // todo sort by name, price in api
        $query = $this->createQueryBuilder('coin')
            ->orderBy('coin.avgPrice', 'DESC');

        return $this->paginate($query, $listRequest?->page, $listRequest?->perPage);
    }

    public function paginate(QueryBuilder $dql, ?int $page = 1, ?int $perPage = null) : PageDTO
    {
        $page = $page ?? 1;
        $perPage = $perPage ?? $this->params->get('listing.limit');

        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        $coins = array_map(fn(Coin $coin) => $this->coinResource->detail($coin),
            iterator_to_array($paginator));

        return new PageDTO(
            $coins,
            $page,
            $perPage,
            $paginator->count()
        );
    }
}
