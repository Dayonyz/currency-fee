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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected currency pair');

        $service = new StubExchangeRatesService();

        $service->getCurrencyRateByDate(
            CurrenciesEnum::Swiss_Franc,
            CurrenciesEnum::US_Dollar,
            new DateTime()
        );
    }
}