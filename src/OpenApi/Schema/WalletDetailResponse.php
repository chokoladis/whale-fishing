<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WalletDetailResponse',
    properties: [
        new OA\Property(
            property: 'wallet',
            properties: [
                new OA\Property(property: 'address', type: 'string', example: '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb'),
                new OA\Property(
                    property: 'coins',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'coin',
                                properties: [
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'symbol', type: 'string'),
                                    new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                                ],
                                type: 'object',
                            ),
                            new OA\Property(property: 'balance', type: 'string', example: '1500000.25'),
                            new OA\Property(property: 'avgPrice', type: 'string', example: '0.95'),
                            new OA\Property(property: 'total', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'pnl', type: 'number', format: 'float', nullable: true),
                        ],
                        type: 'object',
                    ),
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class WalletDetailResponse
{
}
