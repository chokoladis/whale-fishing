<?php

namespace App\Dispatcher;

use App\Enum\User\TypeVerify;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;

class VerificationCodeDispatcher
{
    public function __construct(
        #[Autowire(tag: 'app.verification_sender')]
        private readonly ServiceLocator $serviceLocator
    )
    {
    }

    public function send(TypeVerify $type, UserInterface $user, string $code)
    {
        if (!$this->serviceLocator->has($type->value)) {
            throw new UnsupportedException(sprintf('Сервис %s неподдерживается', $type->value), $type);
        }

        $this->serviceLocator->get($type->value)->send($type, $code);
    }
}
