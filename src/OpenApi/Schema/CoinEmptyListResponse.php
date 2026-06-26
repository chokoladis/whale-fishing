<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CoinEmptyListResponse',
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(), example: []),
    ],
    type: 'object',
)]
final class CoinEmptyListResponse
{
}
