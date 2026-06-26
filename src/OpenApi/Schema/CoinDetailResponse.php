<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CoinDetailResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(
                    property: 'coin',
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Ethereum'),
                        new OA\Property(property: 'symbol', type: 'string', example: 'ETH'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'contractAddress', type: 'string'),
                        new OA\Property(property: 'network', type: 'string', example: 'native'),
                        new OA\Property(property: 'links', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class CoinDetailResponse
{
}
