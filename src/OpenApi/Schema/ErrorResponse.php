<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Ответ с одной или несколькими текстовыми ошибками',
    properties: [
        new OA\Property(
            property: 'errors',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Такая монета не была найдена'],
        ),
    ],
    type: 'object',
)]
final class ErrorResponse
{
}
