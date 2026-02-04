<?php

namespace Src\Services\ExchangeRates;

use DateTime;
use Exception;
use Src\Enums\CurrenciesEnum;
use Src\HttpCilents\Contracts\HttpClientInterface;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;

class OpenExchangeRatesService implements ExchangeRatesContract
{
    private string $endpoint;
    private string $key;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->endpoint = $_ENV['OER_ENDPOINT'];
        $this->key = $_ENV['OER_KEY_ID'];
    }

    /**
     * @throws Exception
     */
    public function getCurrencyRateByDate(
        CurrenciesEnum $currency,
        CurrenciesEnum $baseCurrency,
        DateTime    $date
    ): ExchangeRateResult
    {
        $url = "{$this->endpoint}/historical/" .
            "{$date->format('Y-m-d')}.json" .
            "?app_id={$this->key}&" .
            "base={$baseCurrency->value}&" .
            "symbols={$currency->value}";

        $response = $this->httpClient->get($url);

        $rate = $response['data']['rates'][$currency->value] ?? null;

        if (is_numeric($rate)) {
            return new ExchangeRateResult(
                rate: (float) $rate,
                isApproximate: false
            );
        }

        if (! empty($response['data']['error'])) {
            throw new Exception(
                $response['data']['description'] ?? 'Exchange rate API error'
            );
        }

        throw new Exception(
            'Unexpected API response: ' . json_encode($response)
        );
    }
}