<?php

namespace App\DTO\Http\Response;

readonly class PageDTO
{
    public function __construct(
        public array $items,
        public int $page,
        public int $perPage,
        public int $total,
    ){}
}
