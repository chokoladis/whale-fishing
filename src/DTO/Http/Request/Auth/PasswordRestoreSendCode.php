<?php

namespace App\DTO\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordRestoreSendCode
{
    public function __construct(
        #[Assert\NotBlank(allowNull: null)]
        #[Assert\Email(message: 'Необходимо ввести валидный email')]
        public string $email
    )
    {
    }
}
