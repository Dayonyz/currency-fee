<?php

namespace Tests\Integration;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\ExchangeRates\OpenExchangeRatesService;

class OpenExchangeRatesServiceIntegrationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetCurrencyRateByDateRealRequest()
    {
        $httpClient = new CurlHttpClient();
        $service = new OpenExchangeRatesService($httpClient);

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::from('EUR'),
            CurrenciesEnum::from('USD'),
            new DateTime()
        );

        $this->assertIsFloat($result->rate);
        $this->assertGreaterThan(0, $result->rate);
    }
}

