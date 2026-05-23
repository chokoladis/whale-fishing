<?php

namespace App\Enum\Coin;

enum LinkType : string
{
    case WEB = 'web';
    case YOUTUBE = 'youtube';
    case TELEGRAM = 'telegram';
    case FACEBOOK = 'facebook';
    case DISCORD = 'discord';
    case X = 'x';
    case CMC = 'coinmarketcap';
}
