<?php

namespace App\Dispatcher;

use App\Enum\User\TypeVerify;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;

class VerificationCodeDispatcher
{
    public function __construct(
        #[AutowireLocator('app.verification_sender')]
        private readonly iterable $senders
    )
    {
    }

    public function send(TypeVerify $type, UserInterface $user, string $code)
    {
        foreach ($this->senders as $raw) {
            if ($raw->support($type)) {
                $raw->send($user, $code);
                return;
            }
        }

        throw new UnsupportedException(sprintf('Сервис %s неподдерживается или отключен', $type->value), $type);
    }
}
