<?php

namespace Tests\Unit;

use DateTime;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\ExchangeRates\OpenExchangeRatesService;

class OpenExchangeRatesServiceTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function tesGetCurrencyRateByDateReturnsCorrectRate()
    {
        $mockHttpClient = $this->createMock(CurlHttpClient::class);
        $mockHttpClient->method('get')
            ->willReturn(['data' =>  ['rates' => ['EUR' => 1.2]]]);

        $service = new OpenExchangeRatesService($mockHttpClient);
        $rate = $service->getCurrencyRateByDate(
            CurrenciesEnum::from('EUR'),
            CurrenciesEnum::from('USD'),
            new DateTime('2023-01-01')
        );

        $this->assertEquals(1.2, $rate);
    }

    /**
     * @throws Exception
     */
    public function testGetCurrencyRateByDateThrowsExceptionOnInvalidResponse()
    {
        $mockHttpClient = $this->createMock(CurlHttpClient::class);
        $mockHttpClient->method('get')
            ->willReturn(['rates' => []]);

        $service = new OpenExchangeRatesService($mockHttpClient);

        $this->expectException(\Exception::class);
        $service->getCurrencyRateByDate(
            CurrenciesEnum::from('EUR'),
            CurrenciesEnum::from('USD'),
            new DateTime('2023-01-01')
        );
    }
}