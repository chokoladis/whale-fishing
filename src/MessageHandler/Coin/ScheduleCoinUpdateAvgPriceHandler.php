<?php

namespace App\MessageHandler\Coin;

use App\Entity\Coin;
use App\Messages\Coin\ScheduleCoinUpdateAvgPriceMessage;
use App\Repository\CoinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ScheduleCoinUpdateAvgPriceHandler
{

    function __construct(
        private CoinRepository         $coinRepository,
        private LoggerInterface        $logger,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(ScheduleCoinUpdateAvgPriceMessage $message): void
    {
        $coins = $this->coinRepository->getByUpdatedAtBefore(new \DateTimeImmutable('-5 minutes'));

        try {
            $this->entityManager->beginTransaction();
            foreach ($coins as $coin) {
                /**
                 * @var Coin $coinObj
                 */
                $coinObj = $coin[0];
                $coinObj->setAvgPrice($coin['avgPrice']);
                $coinObj->setUpdatedAt(new \DateTimeImmutable());
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->entityManager->rollBack();
            return;
        }

        $this->logger->alert('Успешно обновленно');
    }
}
