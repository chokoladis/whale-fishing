<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RateLimitErrorResponse',
    properties: [
        new OA\Property(
            property: 'errors',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Too many requests. Please try again later.'],
        ),
    ],
    type: 'object',
)]
final class RateLimitErrorResponse
{
}
