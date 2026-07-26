<?php

namespace App\EventListener\Auth;

use App\Service\Auth\TokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RefreshTokenListener
{
    public function __construct(
        private TokenService    $tokenService,
        private LoggerInterface $logger,
        private RequestStack    $requestStack,
    )
    {
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        // todo проверка защиты и просьба 2х факторной
        try {
            $refreshToken = $this->tokenService->createRefreshToken($this->requestStack->getCurrentRequest(), $event->getUser());

            $event->setData([
                'access_token' => $event->getData()['token'],
                'refresh_token' => $refreshToken
            ]);
        } catch (\Throwable $exception) {
            $event->setData([
                'access_token' => $event->getData()['token'],
            ]);
            $this->logger->error('Не удалось добавить refresh_token в ответ авторизации', [$exception->getMessage()]);
        }
    }
}
