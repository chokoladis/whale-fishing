<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FieldErrorResponse',
    description: 'Ошибка с привязкой к конкретному полю',
    properties: [
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'string'),
            example: ['email' => 'User with this email already exists.'],
        ),
    ],
    type: 'object',
)]
final class FieldErrorResponse
{
}
