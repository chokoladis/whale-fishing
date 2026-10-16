<?php

namespace DTO\Http\Response\Auth;

final readonly class TelegramLinkRequestResponse
{
    function __construct(
        public string $code,
        public bool $isNewCode = true,
        public string $baseLink = 'https://t.me/whale_fishing_bot'
    )
    {
    }
}
