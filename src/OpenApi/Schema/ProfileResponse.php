<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileResponse',
    properties: [
        new OA\Property(
            property: 'user',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Alice'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class ProfileResponse
{
}
