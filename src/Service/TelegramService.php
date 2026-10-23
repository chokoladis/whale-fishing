<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User\TelegramRequest;
use App\Entity\User\User;
use App\Entity\User\UserVerification;
use App\Enum\User\Status;
use App\Enum\User\TypeVerify;
use App\Exception\External\IntegrationException;
use App\Repository\User\TelegramRequestRepository;
use App\Repository\User\UserRepository;
use App\Repository\User\UserVerificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\KeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\ReplyKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

class TelegramService
{
    public function __construct(
        private TelegramRequestRepository   $telegramRequestRepository,
        private UserRepository              $userRepository,
        private LoggerInterface             $logger,
        private string                      $telegramBotSecretToken,
        private ChatterInterface            $chatter,
        private UserPasswordHasherInterface $passwordHasher,
        private UserService                 $userService,
        private UserVerificationRepository  $userVerificationRepository,
        protected EntityManagerInterface    $manager
    )
    {

    }

    private function saveRequest(Request $request, array $message)
    {
        $telegramChatId = $message['from']['id'];
        $ipAddress = $request->getClientIp(); // todo ?

        $telegramRequest = $this->telegramRequestRepository->findByChatId($telegramChatId);

        if ($telegramRequest) {
            $this->logger->debug('created tg requests', [$telegramRequest]);
//            throw new TelegramAlreadyConnectedException;
        } else {
            $newRequest = new TelegramRequest();
            $newRequest->setIpAddress($ipAddress)->setChatId($telegramChatId);

            $this->telegramRequestRepository->save($newRequest);
        }
    }

    public function handleWebhook(Request $request)
    {
        if ($this->telegramBotSecretToken !== $request->headers->get('x-telegram-bot-api-secret-token')) {
            throw new IntegrationException('Not valid telegram bot token');
        }

        // todo роверка update_id с блокировкой - update_id\":468465099
        //        $request->getPayload()->get('update_id')

        $message = $request->getPayload()->all('message');
        if (!empty($message['text'])) {
            if ($message['text'] === '/start') {

                $this->saveRequest($request, $message);

                $this->chatter->send(new ChatMessage(
                    'Предоставьте доступ к телефону для верификации',
                    (new TelegramOptions())
                        ->chatId((string)$message['from']['id'])
                        ->replyMarkup(
                            (new ReplyKeyboardMarkup())
                                ->oneTimeKeyboard(true)
                                ->resizeKeyboard(true)
                                ->keyboard([
                                    (new KeyboardButton('Подключиться'))->requestContact(true)
                                ])
                        )),
                );

                return null;
            } else {
                $this->chatter->send(new ChatMessage(
                    'Неизвестная команда',
                    (new TelegramOptions())->chatId((string)$message['from']['id']),
                ));
            }
        } else if (!empty($message['contact'])) {
            return $this->confirm($request, $message['from'], $message['contact']);
        }

        return null;
    }

    public function confirm(
        Request $request,
        array   $from,
        array   $contact,
    )
    {
        $phone = $contact['phone_number'];

        $telegramRequest = $this->telegramRequestRepository->findByChatId($contact['user_id']);
        if (!$telegramRequest) {
            throw new EntityNotFoundException('Telegram request not found');
        } elseif ($user = $telegramRequest->getUser()) {
            // todo check phone and do what ?
            // todo не генерить несколько раз
            // генерить ссылку в бот на сайт-фронт с полезной нагрузкой
            $this->chatter->send(new ChatMessage(
                'Успешная авторизация',
                (new TelegramOptions())->chatId((string)$contact['user_id']),
            ));

            return $this->userService->getTokenDataByUser($request, $user);
        } else {
            $user = $this->userRepository->findOneBy([
                'phone' => $phone,
            ]);

            //todo transaction

            $connection = $this->manager->getConnection();
            $connection->setAutoCommit(false);
            $connection->beginTransaction();

            try {
                if (!$user) {
                    $user = new User();
                    $user->setPhone($phone)
                        ->setName(trim(sprintf('%s %s', $from['first_name'] ?? '', $from['last_name'] ?? '')))
                        ->setStatus(Status::ACTIVE)
                        ->setPassword($this->passwordHasher->hashPassword($user, ByteString::fromRandom()->toString()));
                    $this->userRepository->save($user, $this->manager);
                }

                $telegramRequest->setUser($user);

                if (!$user->isTelegramVerified()){
                    $userVerification = new UserVerification();
                    $userVerification->setUser($user)
                        ->setCode(Uuid::v7()->toString())
                        ->setType(TypeVerify::TELEGRAM)
                        ->setVerifiedAt(new \DateTimeImmutable());

                    $this->userVerificationRepository->save($userVerification, $this->manager);
                }

                $connection->commit();
            } catch (Exception $e) {
                $connection->rollback();
                throw $e;
            }

            $this->chatter->send(new ChatMessage(
                'Успешная авторизация',
                (new TelegramOptions())->chatId((string)$contact['user_id']),
            ));

            return $this->userService->getTokenDataByUser($request, $user);
        }
    }
}
