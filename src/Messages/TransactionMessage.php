<?php

namespace App\Messages;

use App\DTO\Http\Response\TransactionDTO;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class TransactionMessage
{
    public function __construct(
        public TransactionDTO $dto,
    )
    {
    }
}
