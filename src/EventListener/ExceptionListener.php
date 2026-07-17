<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: 'kernel.exception', priority: 100)]
final class ExceptionListener
{
    public function __construct(
        private TranslatorInterface $translator,
    )
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();

        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            $event->setResponse(new JsonResponse([
                'errors'  => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY));

            return;
        } else if ($exception instanceof TooManyRequestsHttpException) {
            $event->setResponse(new JsonResponse([
                'errors'  => [
                    $exception->getMessage() ? $exception->getMessage() : $this->translator->trans('error.many_request'),
                ],
            ],$exception->getStatusCode()));

            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $response = new JsonResponse([
            'errors'   => [ $exception->getMessage() ],
        ], $statusCode);

        $event->setResponse($response);
    }
}

