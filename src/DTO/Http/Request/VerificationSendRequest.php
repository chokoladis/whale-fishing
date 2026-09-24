<?php

namespace App\DTO\Http\Request;

use App\Enum\User\TypeVerify;
use Symfony\Component\Validator\Constraints\NotBlank;

readonly class VerificationSendRequest
{
    function __construct(
        #[NotBlank]
        public TypeVerify $typeVerify
    )
    {
    }
}
