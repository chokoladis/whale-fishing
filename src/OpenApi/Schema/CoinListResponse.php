<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CoinListResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Tether USD'),
                            new OA\Property(property: 'symbol', type: 'string', example: 'USDT'),
                            new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'contractAddress', type: 'string'),
                            new OA\Property(property: 'network', type: 'string', example: 'native'),
                            new OA\Property(property: 'links', type: 'array', items: new OA\Items(type: 'object')),
                        ],
                        type: 'object',
                    ),
                ),
                new OA\Property(property: 'page', type: 'integer', example: 1),
                new OA\Property(property: 'perPage', type: 'integer', example: 20),
                new OA\Property(property: 'total', type: 'integer', example: 100),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class CoinListResponse
{
}
