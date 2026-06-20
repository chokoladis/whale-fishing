<?php

namespace App\Resource;

use App\Entity\Coin;
use App\Entity\User;

class ProfileResource
{

    public function profile(
        User $user,
    ) : array
    {
        return [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
}
