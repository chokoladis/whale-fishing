<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransactionListResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(
                    property: 'items',
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
                new OA\Property(property: 'page', type: 'integer', example: 1),
                new OA\Property(property: 'perPage', type: 'integer', example: 20),
                new OA\Property(property: 'total', type: 'integer', example: 500),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class TransactionListResponse
{
}
