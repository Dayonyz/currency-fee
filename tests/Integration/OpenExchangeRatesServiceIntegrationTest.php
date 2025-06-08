<?php

namespace Tests\Integration;

use DateTime;
use PHPUnit\Framework\TestCase;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\ExchangeRates\OpenExchangeRatesService;

class OpenExchangeRatesServiceIntegrationTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test_get_currency_rate_by_date_real_request()
    {
        $httpClient = new CurlHttpClient();
        $service = new OpenExchangeRatesService($httpClient);

        $rate = $service->getCurrencyRateByDate('EUR', 'USD', new DateTime());

        $this->assertIsFloat($rate);
        $this->assertGreaterThan(0, $rate);
    }
}
