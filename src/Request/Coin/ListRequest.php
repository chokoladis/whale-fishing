<?php

namespace App\Request\Coin;

use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Range;

readonly class ListRequest
{
    public function __construct(
        #[IsNull]
        #[Range(min: 1)]
        public ?int $page = null,
        #[IsNull]
        #[Range(min: 5, max: 30)]
        public ?int $perPage = null,
        #[IsNull]
        // todo check
        public ?string $sort = null,
        #[IsNull]
        public ?string $order = null,
    )
    {
    }

}
