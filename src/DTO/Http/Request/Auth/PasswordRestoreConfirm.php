<?php

namespace App\DTO\Http\Request\Auth;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class PasswordRestoreConfirm
{
    public function __construct(
        #[NotBlank]
        #[Email]
        public string $email,
        #[NotBlank]
        public string $token,
        #[NotBlank]
        #[PasswordStrength(minScore: 1)]
        public string $password,
        #[EqualTo(propertyPath: 'password', message: 'Passwords do not match.')]
        public string $password_confirm,
    ){}
}
