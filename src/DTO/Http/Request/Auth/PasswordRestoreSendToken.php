<?php

namespace App\DTO\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordRestoreSendToken
{
    public function __construct(
        #[Assert\Email(message: 'Необходимо ввести валидный email')]
        public ?string $email = null,
        // todo regexp
        public ?string $phone = null,
    )
    {
    }
}
