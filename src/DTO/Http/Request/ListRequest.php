<?php

namespace App\DTO\Http\Request;

use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Range;

readonly class ListRequest
{
    public function __construct(
        #[Range(min: 1)]
        public ?int $page = null,
        #[Range(min: 5, max: 30)]
        public ?int $perPage = null,
        // todo check
        public ?string $sort = null,
        public ?string $order = null,
    )
    {
    }

}
