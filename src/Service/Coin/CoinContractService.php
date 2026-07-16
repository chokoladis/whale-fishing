<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\DTO\Http\Response\Coin\CoinDetailResponse;
use App\Entity\Coin;
use App\Entity\CoinContract;
use App\Repository\CoinContractRepository;
use Psr\Log\LoggerInterface;

class CoinContractService
{
    public function __construct(
        private CoinContractRepository $coinContractRepository,
        private LoggerInterface $logger,
    )
    {
    }


    public function updateByCoinDetailResponse(Coin $coin, CoinDetailResponse $coinDetailResponse)
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
        }  else if ($coin->getCoinContract()->count() != count($coinDetailResponse->coinContracts)){
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
                    $newContract->setContractAddress($coinContract->contractAddress);
                    $newContract->setDecimal($coinContract->decimal);
                }

                $this->coinContractRepository->save($newContract);
            }
        }
    }
}
