<?php

namespace Tests\Unit;

use DateTime;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\FallbackExchangeRatesService;
use Src\Services\ExchangeRates\Contracts\ExchangeRatesContract;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;

class FallbackExchangeRatesServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testUsesPrimaryServiceWhenItWorks()
    {
        $primary = $this->createMock(ExchangeRatesContract::class);
        $fallback = $this->createMock(ExchangeRatesContract::class);

        $primary->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->willReturn(new ExchangeRateResult(1.11, false));

        $fallback->expects($this->never())
            ->method('getCurrencyRateByDate');

        $service = new FallbackExchangeRatesService($primary, $fallback);

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::Euro,
            CurrenciesEnum::US_Dollar,
            new DateTime()
        );

        $this->assertFalse($result->isApproximate);
        $this->assertSame(1.11, $result->rate);
    }

    public function testFallsBackWhenPrimaryThrowsException()
    {
        $primary = $this->createMock(ExchangeRatesContract::class);
        $fallback = $this->createMock(ExchangeRatesContract::class);

        $primary->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->willThrowException(new \RuntimeException('API limit'));

        $fallback->expects($this->once())
            ->method('getCurrencyRateByDate')
            ->willReturn(new ExchangeRateResult(0.99, true));

        $service = new FallbackExchangeRatesService($primary, $fallback);

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::Euro,
            CurrenciesEnum::US_Dollar,
            new DateTime()
        );

        $this->assertTrue($result->isApproximate);
        $this->assertSame(0.99, $result->rate);
    }
}