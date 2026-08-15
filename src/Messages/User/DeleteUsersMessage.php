<?php

namespace App\Messages\User;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class DeleteUsersMessage
{
    public function __construct(
    )
    {
    }
}
