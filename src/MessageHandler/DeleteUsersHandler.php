<?php

namespace App\MessageHandler;

use App\Messages\User\DeleteUsersMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteUsersHandler
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {

    }

    public function __invoke(DeleteUsersMessage $message): void
    {
        $this->userRepository->forceDeleteUsers();
    }
}