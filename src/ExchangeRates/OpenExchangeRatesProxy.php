<?php

namespace Src\ExchangeRates;

use Exception;
use Src\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Http\CurlHttpClient;

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
    public function getCurrencyRateByDate(string $currency, string $baseCurrency, \DateTime $date): float
    {
        $key = "{$currency}_{$baseCurrency}";

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