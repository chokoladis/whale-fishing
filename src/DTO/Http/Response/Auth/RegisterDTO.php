<?php

namespace App\DTO\Http\Response\Auth;

readonly class RegisterDTO
{
    public function __construct(
        /** @var array<string, string> $user - from resource */
        public array $user,
        public string $token,
    )
    {
    }
}
