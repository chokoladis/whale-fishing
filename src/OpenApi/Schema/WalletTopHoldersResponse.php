<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WalletTopHoldersResponse',
    properties: [
        new OA\Property(
            property: 'wallets',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'address', type: 'string'),
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
                                new OA\Property(property: 'balance', type: 'string'),
                                new OA\Property(property: 'avgPrice', type: 'string'),
                                new OA\Property(property: 'total', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'pnl', type: 'number', format: 'float', nullable: true),
                            ],
                            type: 'object',
                        ),
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final class WalletTopHoldersResponse
{
}
