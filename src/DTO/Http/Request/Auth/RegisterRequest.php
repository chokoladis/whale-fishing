<?php

namespace App\DTO\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        // todo check
        #[Assert\Length(min: 6, max: 15)]
        public string $phone,
        #[Assert\NotBlank]
        #[Assert\PasswordStrength(minScore: 1)]
        public string  $password,
        #[Assert\EqualTo(propertyPath: 'password', message: 'Passwords do not match.')]
        public string  $password_confirm,
        public ?string $email = null,
        public ?string $name = null,
    )
    {
    }
}
