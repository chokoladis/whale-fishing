<?php

namespace App\DTO\Http\Response;

readonly class TransactionDTO
{
    public function __construct(
        public string $blockNumber,
        public string $hash,
        public string $from,
        public string $to,
        public string $contractAddress,
        public string $amount,
    )
    {
    }
}
