<?php

namespace App;

use App\Messages\CleanPasswordRestoreMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
final class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return (new SymfonySchedule())
            ->add(RecurringMessage::every('1 day', new CleanPasswordRestoreMessage()))
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run
        ;
    }
}
