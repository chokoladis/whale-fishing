<?php

namespace App\DTO\Http\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\PasswordStrength(minScore: 1)]
        public string $password,
        #[Assert\EqualTo(propertyPath: 'password', message: 'Passwords do not match.')]
        public string $password_confirm,
    )
    {
    }
}
