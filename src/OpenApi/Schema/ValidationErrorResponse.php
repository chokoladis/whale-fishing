<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    description: 'Ошибки валидации полей (HTTP 422)',
    properties: [
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'string'),
            example: ['email' => 'This value is not a valid email address.', 'password' => 'This value should not be blank.'],
        ),
    ],
    type: 'object',
)]
final class ValidationErrorResponse
{
}
