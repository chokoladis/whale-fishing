<?php

declare(strict_types=1);

namespace App\Service\Coin;


use App\DTO\Http\Response\Coin\CoinStatisticsResponse;
use App\Entity\Coin;
use App\Entity\CoinDetail;
use App\Repository\CoinDetailRepository;
use Psr\Log\LoggerInterface;

class CoinDetailService
{
    public function __construct(
        private CoinDetailRepository $coinDetailRepository,
        private LoggerInterface $logger,
    )
    {
    }


    public function updateByCoinDetailResponse(Coin $coin, CoinStatisticsResponse $statistics)
    {
        $coinDetail = new CoinDetail();
        $coinDetail->setCoin($coin);
        $coinDetail->setMarketCap($statistics->marketCap);
        $coinDetail->setVolume($statistics->volume);
        $coinDetail->setLiquidity($statistics->liquidity);
        $coinDetail->setTotalSupply($statistics->totalSupply);
        $coinDetail->setCirculationSupply($statistics->circulationSupply);

        if ($statistics->maxSupply)
            $coinDetail->setMaxSupply($statistics->maxSupply);

        $this->coinDetailRepository->save($coinDetail);
    }
}
