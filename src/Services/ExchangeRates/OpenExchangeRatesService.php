<?php

namespace Src\Services\ExchangeRates;

use Exception;
use Src\HttpCilents\Contracts\HttpClientInterface;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;

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

        if (!isset($response['data']['rates']) || !isset($response['data']['rates'][$currency]))
            throw new Exception(json_encode($response));

        return $response['data']['rates'][$currency];
    }
}