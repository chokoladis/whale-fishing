<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransactionTopResponse',
    properties: [
        new OA\Property(
            property: 'data',
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
                    new OA\Property(property: 'hash', type: 'string'),
                    new OA\Property(property: 'from', type: 'string'),
                    new OA\Property(property: 'to', type: 'string'),
                    new OA\Property(property: 'amount', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string'),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final class TransactionTopResponse
{
}
