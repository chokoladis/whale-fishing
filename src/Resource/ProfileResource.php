<?php

namespace App\Resource;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileResource
{

    /**
     * @param User $user
     * @return array<string, string|array<string,string>>
     */
    public function profile(
        User $user,
    ): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }

    /**
     * @param UserInterface $user
     * @return array<string, mixed>
     */
    public function fullData(UserInterface $user): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'status' => $user->getStatus()->value,
        ];
    }
}
