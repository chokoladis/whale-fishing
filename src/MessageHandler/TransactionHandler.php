<?php

namespace App\MessageHandler;

use App\Message\WSSTransaction;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TransactionHandler
{
    public function __invoke(WSSTransaction $transaction)
    {
        // ... do some work - like sending an SMS message!
    }
}
