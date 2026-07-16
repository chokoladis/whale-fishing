<?php

declare(strict_types=1);

namespace App\DTO\Http\Response\Coin;

readonly class CoinDetailResponse
{
    /**
     * @param string $name
     * @param string $symbol
     * @param int $decimals
     * @param string $price
     * @param CoinStatisticsResponse $statistics
     * @param array<int, CoinContractResponse>|null $coinContracts
     */
    public function __construct(
        public string $name,
        public string $symbol,
        public int $decimals,
        public string $price,
        public CoinStatisticsResponse $statistics,
        public ?array $coinContracts = null,
    )
    {
    }
}
