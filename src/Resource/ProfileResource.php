<?php

namespace App\Resource;

use App\Entity\Coin;
use App\Entity\User;

class ProfileResource
{

    /**
     * @param User $user
     * @return array<string, string|array<string,string>>
     */
    public function profile(
        User $user,
    ) : array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
}
