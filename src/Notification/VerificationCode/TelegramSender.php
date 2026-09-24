<?php

namespace App\Notification\VerificationCode;

use App\Interface\Notification\VerificationCodeSender;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Security\Core\User\UserInterface;

class TelegramSender implements VerificationCodeSender
{
    public function __construct(
        private ChatterInterface $chatter
    )
    {
    }

    public function send(UserInterface $user, string $code): void
    {
        $chatId = $user->getTelegramChatId();
        if (!$chatId) {
            throw new \DomainException('У пользователя не привязан Telegram');
        }

        $message = new ChatMessage(
            sprintf('Ваш код верификации: %s', $code),
            (new TelegramOptions())->chatId($chatId)
        );

        $this->chatter->send($message);
    }
}
