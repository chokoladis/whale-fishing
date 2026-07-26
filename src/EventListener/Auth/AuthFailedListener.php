<?php

namespace App\EventListener\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthFailedListener extends AuthenticationFailureHandler
{
    public function __construct(
//        private LoggerInterface $logger,
        EventDispatcherInterface    $dispatcher,
        private TranslatorInterface $translator,
    )
    {
        parent::__construct($dispatcher, $translator);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
            return new JsonResponse([
                'errors' => [
                    $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security')
                ]
            ], Response::HTTP_TOO_MANY_REQUESTS);
        } else if ($exception instanceof UserNotFoundException || $exception instanceof BadCredentialsException) {
            return new JsonResponse([
                'errors' => [
                    $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security')
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
