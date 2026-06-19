<?php

namespace App\Interface\External;

use App\Exception\External\RateLimitException;
use Symfony\Component\HttpKernel\Exception\HttpException;

interface GetterPriceInterface
{
    /**
     * @throws HttpException
     * @throws RateLimitException
     */
    public function getPriceBySymbol(string $symbol) : float;
    /**
     * @throws HttpException
     * @throws RateLimitException
     */
    public function getPriceByContractAddress(string $contractAddress) : float;
}
