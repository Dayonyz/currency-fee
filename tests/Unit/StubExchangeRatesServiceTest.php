<?php

namespace Tests\Unit;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\Services\ExchangeRates\StubExchangeRatesService;

class StubExchangeRatesServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testReturnsStubRateForKnownPair()
    {
        $service = new StubExchangeRatesService();

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::US_Dollar,
            CurrenciesEnum::Euro,
            new DateTime()
        );

        $this->assertTrue($result->isApproximate);
        $this->assertSame(1.2, $result->rate);

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::Yen,
            CurrenciesEnum::Euro,
            new DateTime()
        );

        $this->assertTrue($result->isApproximate);
        $this->assertSame( 184.9, $result->rate);

        $result = $service->getCurrencyRateByDate(
            CurrenciesEnum::Pound_Sterling,
            CurrenciesEnum::Euro,
            new DateTime()
        );

        $this->assertTrue($result->isApproximate);
        $this->assertSame( 0.86, $result->rate);
    }

    public function testThrowsExceptionForUnknownPair()
    {
        $service = new StubExchangeRatesService();

        $baseCurrency = CurrenciesEnum::US_Dollar;
        $currency = CurrenciesEnum::Swiss_Franc;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unexpected currency pair for Stub: {$baseCurrency->value}_{$currency->value}");

        $service->getCurrencyRateByDate(
            $currency,
            $baseCurrency,
            new DateTime()
        );
    }
}