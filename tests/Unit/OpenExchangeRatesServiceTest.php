<?php

namespace Tests\Unit;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\ExchangeRates\OpenExchangeRatesService;
use Src\Http\CurlHttpClient;
use DateTime;

class OpenExchangeRatesServiceTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function test_get_currency_rate_by_date_returns_correct_rate()
    {
        $mockHttpClient = $this->createMock(CurlHttpClient::class);
        $mockHttpClient->method('get')
            ->willReturn(['data' =>  ['rates' => ['EUR' => 1.2]]]);

        $service = new OpenExchangeRatesService($mockHttpClient);
        $rate = $service->getCurrencyRateByDate('EUR', 'USD', new DateTime('2023-01-01'));

        $this->assertEquals(1.2, $rate);
    }

    /**
     * @throws Exception
     */
    public function test_get_currency_rate_by_date_throws_exception_on_invalid_response()
    {
        $mockHttpClient = $this->createMock(CurlHttpClient::class);
        $mockHttpClient->method('get')
            ->willReturn(['rates' => []]);

        $service = new OpenExchangeRatesService($mockHttpClient);

        $this->expectException(\Exception::class);
        $service->getCurrencyRateByDate('EUR', 'USD', new DateTime('2023-01-01'));
    }
}