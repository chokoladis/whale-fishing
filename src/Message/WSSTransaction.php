<?php

namespace App\Message;

use App\DTO\Http\Response\TransactionDTO;

class WSSTransaction
{
    public function __construct(
        private TransactionDTO $transactionDTO,
    )
    {
    }

    public function getTransactionDTO(): TransactionDTO
    {
        return $this->transactionDTO;
    }
}
