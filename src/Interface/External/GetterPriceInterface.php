<?php

namespace App\Interface\External;

interface GetterPriceInterface
{
    public function getPriceBySymbol(string $symbol) : float;
    public function getPriceByContractAddress(string $contractAddress) : float;
}
