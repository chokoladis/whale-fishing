<?php

namespace App\Messages\Coin;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class ScheduleCoinUpdateAvgPriceMessage
{

}