<?php

declare(strict_types=1);

namespace App\Interface\Notification;

use App\Entity\User\User;
use App\Enum\User\Channel;

interface SendCodeDispatcher
{
    public function send(User $user, string $code, string $nameOperation) : void;
    public function support(Channel $type): bool; //todo делибльная проверка, переделать
}
