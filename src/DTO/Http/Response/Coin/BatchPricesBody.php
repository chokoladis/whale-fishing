<?php

namespace App\DTO\Http\Response\Coin;

final readonly class BatchPricesBody
{
    /**
     * @param array<BatchPricesPayloadItem> $payload
     */
    public function __construct(
        public array $payload,
    ){
    }
}