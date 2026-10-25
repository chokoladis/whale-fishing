<?php

namespace App\Notification\SendCode;

use App\Entity\User\User;
use App\Enum\User\Channel;
use App\Interface\Notification\SendCodeDispatcher;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class TelegramDispatcher implements SendCodeDispatcher
{
    public function __construct(
        private ChatterInterface $chatter
    )
    {
    }

    #[\Override]
    public function send(User $user, string $code,  string $nameOperation): void
    {
        $chatId = $user->getTelegramRequest()?->getChatId();
        if (!$chatId) {
            throw new NotFoundResourceException('У пользователя не привязан Telegram');
        }

        $message = new ChatMessage(
            sprintf('Ваш код для %s: %s', $nameOperation, $code),
            (new TelegramOptions())->chatId($chatId)
        );

        $this->chatter->send($message);
    }

    public function support(Channel $type): bool
    {
        return $type === Channel::TELEGRAM;
    }
}
