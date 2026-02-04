<?php

namespace Src\Services\ExchangeRates;

use DateTime;
use Exception;
use Src\Enums\CurrenciesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;

class OpenExchangeRatesProxy implements ExchangeRatesContract
{
    private ExchangeRatesContract $sourceService;
    private array $cache;

    public function __construct(?ExchangeRatesContract $sourceService = null)
    {
        $this->sourceService = $sourceService ? : new OpenExchangeRatesService(new CurlHttpClient());
        $this->cache = [];
    }

    /**
     * @throws Exception
     */
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency, DateTime $date
    ): ExchangeRateResult {
        $key = "{$currency->value}_{$baseCurrency->value}_{$date->format('Y-m-d')}";

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        } else {
            $this->cache[$key] = $this->sourceService->getCurrencyRateByDate(
                $currency,
                $baseCurrency,
                $date
            );
        }

        return $this->cache[$key];
    }
}