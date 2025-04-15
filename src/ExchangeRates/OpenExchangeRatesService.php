<?php

namespace Src\ExchangeRates;

use Exception;
use Src\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Http\Contracts\HttpClientInterface;

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
        string $currency,
        string $baseCurrency,
        \DateTime    $date
    ): float
    {
        $url = "{$this->endpoint}/historical/" .
            "{$date->format('Y-m-d')}.json" .
            "?app_id={$this->key}&" .
            "base={$baseCurrency}&" .
            "symbols={$currency}";

        $response = $this->httpClient->get($url);

        if (!isset($response['rates']) || !isset($response['rates'][$currency]))
            throw new Exception(json_encode($response));

        return $response['rates'][$currency];
    }
}