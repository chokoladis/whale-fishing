<?php

namespace App\DTO\Http\Request;

use App\Enum\User\Channel;
use Symfony\Component\Validator\Constraints\NotBlank;

readonly class VerificationSendRequest
{
    function __construct(
        #[NotBlank]
//        #[Type(TypeVerify::class, 'Неподдерживаемый тип верификации')] // todo
        public Channel $typeVerify
    )
    {
    }
}
