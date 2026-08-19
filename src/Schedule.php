<?php

namespace App;

use App\Messages\Coin\ScheduleCoinContractsUpdaterMessage;
use App\Messages\Coin\ScheduleCoinUpdateAvgPriceMessage;
use App\Messages\User\CleanPasswordRestoreMessage;
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
    )
    {
    }

    public function getSchedule(): SymfonySchedule
    {
        return (new SymfonySchedule())
            ->add(RecurringMessage::every('1 day', new CleanPasswordRestoreMessage()))
            ->add(RecurringMessage::every('5 minutes', new ScheduleCoinContractsUpdaterMessage()))
            ->add(RecurringMessage::every('5 minutes', new ScheduleCoinUpdateAvgPriceMessage()))
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run
            ;
    }
}
