<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TelegramLinkRequest;
use App\Repository\TelegramLinkRequestRepository;
use DTO\Http\Response\Auth\TelegramLinkRequestResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class TelegramService
{
    public function __construct(
        private TelegramLinkRequestRepository $telegramLinkRequestRepository,
        private LoggerInterface $logger,
        private string $telegramBotSecretToken,
    )
    {

    }

    public function getLinkRequest(string $ip) : TelegramLinkRequestResponse
    {
        $collection = $this->telegramLinkRequestRepository->findNoExpiredByIpAddress($ip);
        if (count($collection)) {
            if (count($collection) === 1) {
                $row = current($collection);
                $diff = (new \DateTimeImmutable())->getTimestamp() - $row->getCreatedAt()->getTimestamp();
                if ($diff > 360) {
                    $newRequest = new TelegramLinkRequest();
                    $newRequest->setIpAddress($ip)->setUuid(Uuid::v7());
                    $this->telegramLinkRequestRepository->save($newRequest);

                    return new TelegramLinkRequestResponse($newRequest->getUuid()->toString());
                }
            }
            return new TelegramLinkRequestResponse(current($collection)->getUuid()->toString(), false);
        }

        $newRequest = new TelegramLinkRequest();
        $newRequest->setIpAddress($ip)->setUuid(Uuid::v7());
        $this->telegramLinkRequestRepository->save($newRequest);

        return new TelegramLinkRequestResponse($newRequest->getUuid()->toString());
    }

    public function handleWebhook(Request $request)
    {
        if ($this->telegramBotSecretToken !== $request->headers->get('x-telegram-bot-api-secret-token')) {
//            throw new \Exception('');
        }

        // todo роверка update_id с блокировкой - update_id\":468465099
        //        $request->getPayload()->get('update_id')

        $message = $request->getPayload()->all('message');

        Uuid::v7();
        //        "code": "01a0fdc7-c72e-780e-9899-b6b185a76887",
        //    "isNewCode": true,
        //    "baseLink": "https://t.me/whale_fishing_bot"

    }
}
