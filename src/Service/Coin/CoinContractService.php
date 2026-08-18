<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Http\Response\Coin\BatchPricesBody;
use App\DTO\Http\Response\Coin\CoinDetailResponse;
use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Enum\External\ChainId;
use App\Helper\StrHelper;
use App\Repository\CoinContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoinContractService
{
    public function __construct(
        private CoinContractRepository $coinContractRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'monolog.logger.services')]
        private LoggerInterface        $logger,
    )
    {
    }


    public function updateByCoinDetailResponse(Coin $coin, CoinDetailResponse $coinDetailResponse) : void
    {
        if ($coin->getCoinContract()->isEmpty()) {
            foreach ($coinDetailResponse->coinContracts as $coinContract) {

                $newContract = new CoinContract();
                $newContract->setCoin($coin);
                $newContract->setLocalPrice($coin->getAvgPrice());
                $newContract->setNetwork($coinContract->network);
                $newContract->setContractAddress($coinContract->address);
                $newContract->setDecimal($coinContract->decimals);

                $this->coinContractRepository->save($newContract);
            }
        } else if ($coin->getCoinContract()->count() != count($coinDetailResponse->coinContracts)) {
            foreach ($coinDetailResponse->coinContracts as $coinContract) {

                $newContract = $this->coinContractRepository->findByAddressAndNetwork(
                    $coinContract->address,
                    $coinContract->network
                );
                if ($newContract) {
                    $newContract->setLocalPrice($coin->getAvgPrice());
                } else {
                    $newContract = new CoinContract();
                    $newContract->setCoin($coin);
                    $newContract->setLocalPrice($coin->getAvgPrice());
                    $newContract->setNetwork($coinContract->network);
                    $newContract->setContractAddress($coinContract->address);
                    $newContract->setDecimal($coinContract->decimals);
                }

                $this->coinContractRepository->save($newContract);
            }
        }
    }

    public function updatePricesByBatchPrices(BatchPricesBody $batchPricesBody, array $coinContracts) : void
    {
        foreach ($batchPricesBody->payload as $item) {
            if ($chain = ChainId::tryFrom($item->chainId)) {
                $network = $chain->name;
                /**
                 * @var CoinContract $coinContract
                 */
                $coinContract = $coinContracts[strtolower($network.'_'.$item->address)];

                if ($coinContract && $item->priceUSD) {
                    $coinContract->setLocalPrice(
                        StrHelper::trimZeros(StrHelper::toNormalNum($item->priceUSD))
                    );
                    if ($item->marketCapUSD)
                        $coinContract->getCoin()
                            ->getCoinDetail()
                            ->setMarketCap(strval($item->marketCapUSD)); //todo separate full-update in schedule
                    //todo? $coinContract->getCoin()->setAvgPrice()
                }
            }
        }

        $this->entityManager->flush();
    }
}
