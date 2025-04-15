<?php

namespace Src\ExchangeRates\Contracts;

interface ExchangeRatesContract
{
    public function getCurrencyRateByDate(string $currency, string $baseCurrency, \DateTime $date): float;
}