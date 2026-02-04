<?php

namespace Tests\Unit;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;
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
    public function testFetchesRateIfNotCached()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->with(CurrenciesEnum::from('USD'), CurrenciesEnum::from('EUR'), $this->date)
            ->willReturn(new ExchangeRateResult(1.1, false));

        $proxy = new OpenExchangeRatesProxy($mockService);
        $result = $proxy->getCurrencyRateByDate(
            CurrenciesEnum::from('USD'),
            CurrenciesEnum::from('EUR'),
            $this->date
        );

        $this->assertEquals(1.1, $result->rate);
        $this->assertEquals(false, $result->isApproximate);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function tesReturnsRateFromCacheOnSecondCall()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->with(CurrenciesEnum::from('GBP'), CurrenciesEnum::from('EUR'), $this->date)
            ->willReturn(0.85);

        $proxy = new OpenExchangeRatesProxy($mockService);

        $first = $proxy->getCurrencyRateByDate(
            CurrenciesEnum::from('GBP'),
            CurrenciesEnum::from('EUR'),
            $this->date
        );

        $second = $proxy->getCurrencyRateByDate(
            CurrenciesEnum::from('GBP'),
            CurrenciesEnum::from('EUR'),
            $this->date
        );

        $this->assertEquals(0.85, $first->rate);
        $this->assertEquals(0.85, $second->rate);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testThrowsExceptionFromSourceService()
    {
        $mockService = $this->createMock(ExchangeRatesContract::class);

        $mockService->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->willThrowException(new Exception("Failed to fetch rate"));

        $proxy = new OpenExchangeRatesProxy($mockService);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed to fetch rate");

        $proxy->getCurrencyRateByDate(
            CurrenciesEnum::from('JPY'),
            CurrenciesEnum::from('USD'),
            $this->date
        );
    }
}