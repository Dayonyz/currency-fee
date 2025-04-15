<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Src\ExchangeRates\OpenExchangeRatesService;
use Src\Http\CurlHttpClient;
use DateTime;

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
