<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterResponse',
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
        new OA\Property(property: 'token', type: 'string', description: 'JWT access token'),
    ],
    type: 'object',
)]
final class RegisterResponse
{
}
