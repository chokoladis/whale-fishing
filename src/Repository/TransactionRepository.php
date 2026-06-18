<?php

namespace App\Repository;

use App\DTO\Http\Request\ListRequest;
use App\DTO\Http\Response\PageDTO;
use App\DTO\Http\Response\TransactionDTO;
use App\Entity\Coin;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Enum\Coin\TransactionType;
use App\Resource\TransactionResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;


/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $manager,
        private ContainerBagInterface $params,
        private TransactionResource $transactionResource,
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Wallet $wallet, TransactionDTO $transaction, Coin $coin, TransactionType $type): void
    {
        $amount = bcdiv(
            $transaction->amountRaw,
            bcpow('10', (string)$coin->getDecimal()),
            $coin->getDecimal()
        );

        $newTransaction = new Transaction();
        $newTransaction->setBlockNumber($transaction->blockNumber);
        $newTransaction->setHash($transaction->hash);
        $newTransaction->setFrom($transaction->from);
        $newTransaction->setTo($transaction->to);
        $newTransaction->setType($type);
        $newTransaction->setAmount($amount);

        $newTransaction->setWallet($wallet);
        $newTransaction->setCoin($coin);

        $this->manager->persist($newTransaction);
        $this->manager->flush();
    }

    public function getTopTransactionsByTime(\DateTime $dateFrom): array
    {
        // cache, set limit
        return $this->createQueryBuilder('t')
            ->andWhere('t.createdAt >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom)
            ->orderBy('t.amount', 'DESC')
            ->setMaxResults($this->params->get('listing.limit'))
            ->getQuery()
            ->getResult();
    }

    public function getList(?ListRequest $listRequest): PageDTO
    {
        $page = $listRequest->page ?? 1;
        $perPage = $listRequest->perPage ?? $this->params->get('listing.limit');

        $builder = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        $paginator = $this->paginate($builder, $page, $perPage);

        $transactions = array_map(fn(Transaction $transaction) => $this->transactionResource->getShortData($transaction),
            iterator_to_array($paginator));

        return new PageDTO(
            $transactions,
            $page,
            $perPage,
            $paginator->count()
        );
    }

    protected function paginate(QueryBuilder $query, int $page, int $perPage)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        return $paginator;
    }
}
