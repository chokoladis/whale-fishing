<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\External\IntegrationException;
use App\Exception\External\TelegramAlreadyConnectedException;
use App\Repository\User\UserRepository;
use DTO\Http\Response\Auth\TelegramLinkRequestResponse;
use App\Entity\User\TelegramLinkRequest;
use Psr\Log\LoggerInterface;
use App\Repository\User\TelegramLinkRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class TelegramService
{
    public function __construct(
        private TelegramLinkRequestRepository $telegramLinkRequestRepository,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
        private string $telegramBotSecretToken,
        private ChatterInterface $chatter,
    )
    {

    }

    private function getLinkRequest(string $ipAddress, int $telegramChatId, ?string $userEmail) : TelegramLinkRequestResponse
    {
        if ($this->telegramLinkRequestRepository->findRowWithUserByTelegramChatId($telegramChatId)) {
            throw new TelegramAlreadyConnectedException;
        }

        $collection = $this->telegramLinkRequestRepository->findNoExpiredByChatId($telegramChatId);

        $newRequest = new TelegramLinkRequest();
        $newRequest->setIpAddress($ipAddress)->setUuid(Uuid::v7())->setChatId($telegramChatId);

        if ($userEmail) {
            $newRequest->setUser($this->userRepository->getByEmail($userEmail));
        }

        if (count($collection)) {
            if (count($collection) === 1) {
                $row = current($collection);
                $diff = (new \DateTimeImmutable())->getTimestamp() - $row->getCreatedAt()->getTimestamp();
                if ($diff > 360) {
                    $this->telegramLinkRequestRepository->save($newRequest);

                    return new TelegramLinkRequestResponse($newRequest->getUuid()->toString());
                }
            }
            return new TelegramLinkRequestResponse(current($collection)->getUuid()->toString(), false);
        }

        $this->telegramLinkRequestRepository->save($newRequest);

        return new TelegramLinkRequestResponse($newRequest->getUuid()->toString());
    }

    public function handleWebhook(Request $request, ?UserInterface $user)
    {
        if ($this->telegramBotSecretToken !== $request->headers->get('x-telegram-bot-api-secret-token')) {
            throw new IntegrationException('Not valid telegram bot token');
        }

        // todo роверка update_id с блокировкой - update_id\":468465099
        //        $request->getPayload()->get('update_id')
        //            $message['from']['username']

        $message = $request->getPayload()->all('message');
        if ($message['text'] === '/start') {

            // generation and saving code with relation to user|null
            // -> send code to telegram
            // -> user getting a code and send him to | front interface / current service API |

            $res = $this->getLinkRequest($request->getClientIp(), $message['from']['id'], $user?->getUserIdentifier());

            $this->chatter->send(new ChatMessage(
                sprintf('Ваш код: %s', $res->code),
                (new TelegramOptions())->chatId((string)$message['from']['id']),
            ));

            return $res;
        }

        throw new \Exception('test');
    }

    private function confirmTelegramChatId(int $chatId, string $uuid) : void
    {
        //todo
//         if (Uuid::isValid($message['text'])) {
//            $this->confirmTelegramChatId($message['from']['id'], $message['text']);
    }
}
