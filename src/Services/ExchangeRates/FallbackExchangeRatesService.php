<?php

namespace Src\Services\ExchangeRates;

use DateTime;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;
use Throwable;

class FallbackExchangeRatesService implements ExchangeRatesContract
{
    public function __construct(
        private ?ExchangeRatesContract $primary = null,
        private ?ExchangeRatesContract $fallback = null
    ) {
        $this->primary = $primary ?? new OpenExchangeRatesProxy();
        $this->fallback = $fallback ?? new StubExchangeRatesService();
    }

    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        DateTime       $date
    ): ExchangeRateResult {
        try {
            return $this->primary->getCurrencyRateByDate(
                $currency,
                $baseCurrency,
                $date
            );
        } catch (Throwable $e) {
            return $this->fallback->getCurrencyRateByDate(
                $currency,
                $baseCurrency,
                $date
            );
        }
    }
}