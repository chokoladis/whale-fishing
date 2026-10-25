<?php

declare(strict_types=1);

namespace App\Dispatcher;

use App\Enum\User\Channel;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;

class SendCodeDispatcher
{
    public function __construct(
        /**
         * @var array<int, \App\Interface\Notification\SendCodeDispatcher> $senders
         */
        #[AutowireLocator('app.sender_code')]
        private readonly iterable $senders,
    )
    {
    }

    public function send(Channel $type, UserInterface $user, string $code, string $nameOperation)
    {
        foreach ($this->senders as $raw) {
            if ($raw->support($type)) {
                $raw->send($user, $code, $nameOperation);
                return;
            }
        }

        throw new UnsupportedException(sprintf('Сервис %s неподдерживается или отключен', $type->value), $type);
    }
}
