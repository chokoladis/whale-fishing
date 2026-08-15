<?php

namespace App\EventListener\User;

use App\Entity\User;
use App\Enum\User\Status;
use App\Exception\User\UserStatusException;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate)]
class UpdateListener
{
    //todo test
    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        if (!in_array('status', $args->getEntityChangeSet())) {
            $status = Status::tryFrom($args->getOldValue('status'));
            if ($status === Status::BLOCKED || $status === Status::DELETED) {
                throw new UserStatusException();
            }
        }
    }
}