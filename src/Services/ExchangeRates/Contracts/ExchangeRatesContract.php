<?php

namespace Src\Services\ExchangeRates\Contracts;

use DateTime;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;

interface ExchangeRatesContract
{
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency, DateTime $date
    ): ExchangeRateResult;
}