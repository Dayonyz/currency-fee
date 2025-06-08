<?php

namespace Tests\Unit;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\OpenExchangeRatesProxy;

class OpenExchangeRatesProxyTest extends TestCase
{
    private DateTime $date;

    protected function setUp(): void
    {
        $this->date = new DateTime('2024-01-01');
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function test_fetches_rate_if_not_cached()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->with('USD', 'EUR', $this->date)
            ->willReturn(1.1);

        $proxy = new OpenExchangeRatesProxy($mockService);
        $rate = $proxy->getCurrencyRateByDate('USD', 'EUR', $this->date);

        $this->assertEquals(1.1, $rate);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function test_returns_rate_from_cache_on_second_call()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->with('GBP', 'EUR', $this->date)
            ->willReturn(0.85);

        $proxy = new OpenExchangeRatesProxy($mockService);

        $first = $proxy->getCurrencyRateByDate('GBP', 'EUR', $this->date);
        $second = $proxy->getCurrencyRateByDate('GBP', 'EUR', $this->date);

        $this->assertEquals(0.85, $first);
        $this->assertEquals(0.85, $second);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_throws_exception_from_source_service()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->willThrowException(new Exception("Failed to fetch rate"));

        $proxy = new OpenExchangeRatesProxy($mockService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed to fetch rate");

        $proxy->getCurrencyRateByDate('JPY', 'USD', $this->date);
    }
}