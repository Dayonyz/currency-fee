<?php

namespace Src\Services\ExchangeRates\Contracts;

interface ExchangeRatesContract
{
    public function getCurrencyRateByDate(string $currency, string $baseCurrency, \DateTime $date): float;
}