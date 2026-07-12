<?php

namespace App\Interface\External;

use App\Exception\RateLimitException;
use Symfony\Component\HttpKernel\Exception\HttpException;

interface GetterPriceInterface
{
    /**
     * @throws HttpException
     * @throws RateLimitException
     */
    public function getPriceByNetworkAndAddress(string $network, string $contractAddress) : float;
}
