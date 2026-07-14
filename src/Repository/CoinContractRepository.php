<?php

namespace App\Repository;

use App\DTO\Coin\CoinShortDTO;
use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Resource\Coin\CoinResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @extends ServiceEntityRepository<CoinContract>
 */
class CoinContractRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private ContainerBagInterface $params,
        private CoinResource $coinResource,
        private LoggerInterface $logger
    )
    {
        parent::__construct($registry, CoinContract::class);
    }

    public function saveByDTO(CoinShortDTO $coinDTO): CoinContract
    {
        try {
            $coin = new Coin();
            $coin->setSymbol($coinDTO->symbol);
            $coin->setName($coinDTO->symbol);
            $coin->setAvgPrice(0.0);

            $coinContract = new CoinContract();
            $coinContract->setNetwork($coinDTO->network);
            $coinContract->setContractAddress($coinDTO->contractAddress);
            $coinContract->setDecimal($coinDTO->decimal);
            $coinContract->setLocalPrice(0.0);
            $coinContract->setCoin($coin);

            $this->getEntityManager()->persist($coin);
            $this->getEntityManager()->persist($coinContract);
            $this->getEntityManager()->flush();

        } catch (\Throwable $exception) {
            $this->logger->critical('ошибка в coinContract репозитории', [$exception->getMessage(), $coinDTO]);
            throw $exception;
        }

        return $coinContract;
    }

    public function updatePrice(CoinContract $coinContract, float $price): CoinContract
    {
        $coinContract->setLocalPrice($price);

        $this->getEntityManager()->persist($coinContract);
        $this->getEntityManager()->flush();

        return $coinContract;
    }

    public function getList(?ListRequest $listRequest): PageDTO
    {
        // todo sort by name, price in api
        $query = $this->createQueryBuilder('coin')
            ->orderBy('coin.price', 'DESC');

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

    public function findByAddressAndNetwork(string $address, string $network): ?CoinContract
    {
        return $this->findOneBy([
            'contractAddress' => strtolower($address),
            'network' => $network
        ]);
    }
}
