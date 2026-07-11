<?php

namespace App\Messages;

use App\DTO\Http\Response\TransactionDTO;

readonly class TransactionMessage
{
    public function __construct(
        public TransactionDTO $dto,
    )
    {
    }
}
