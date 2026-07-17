<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ApiRateLimitListener
{
    public function __construct(
        private RateLimiterFactory $apiListener,
        private TranslatorInterface $translator,
    )
    {
    }

    #[AsEventListener(priority: 8)]
    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $rateLimiter = $this->apiListener->create($request->getClientIp());
        $limit = $rateLimiter->consume(1);
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(message: $this->translator->trans('error.many_requests'));
        }
    }
}
