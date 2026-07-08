<?php

namespace App\DTO\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordRestoreSendToken
{
    public function __construct(
        #[Assert\NotBlank(allowNull: false)]
        #[Assert\Email(message: 'Необходимо ввести валидный email')]
        public string $email
    )
    {
    }
}
