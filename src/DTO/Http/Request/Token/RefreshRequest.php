<?php

namespace App\DTO\Http\Request\Token;

use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class RefreshRequest
{
    public function __construct(
        #[NotBlank]
        public string $refresh_token,
    )
    {
    }
}
