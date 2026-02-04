<?php

namespace Src\Services\ExchangeRates;

use DateTime;
use Exception;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;

class StubExchangeRatesService implements ExchangeRatesContract
{
    /**
     * @throws Exception
     */
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        DateTime $date
    ): ExchangeRateResult {
        return match ("{$baseCurrency->value}_{$currency->value}") {
            'EUR_EUR' => new ExchangeRateResult(1, true),
            'EUR_USD' => new ExchangeRateResult(1.2, true),
            'EUR_JPY' => new ExchangeRateResult(184.9, true),
            'EUR_GBP' => new ExchangeRateResult(0.86, true),
            default => throw new Exception(
                "Unexpected currency pair for Stub: {$baseCurrency->value}_{$currency->value}"
            ),
        };
    }
}