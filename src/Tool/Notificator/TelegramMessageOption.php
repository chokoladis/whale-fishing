<?php

namespace App\Tool\Notificator;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class TelegramMessageOption implements MessageOptionsInterface
{
    public function __construct(
        public string $chatId
    )
    {

    }

    #[\Override] public function toArray(): array
    {
        return [
            'recipient_id' => $this->getRecipientId(),
            'chat_id' => $this->chatId,
        ];
    }

    #[\Override] public function getRecipientId(): ?string
    {
        return '11';//todo
    }
}
