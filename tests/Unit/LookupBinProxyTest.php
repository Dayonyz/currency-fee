<?php

namespace Tests\Unit;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\LookupBin\LookupBinProxy;
use Src\LookupBin\Contracts\LookupBinInterface;

class LookupBinProxyTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function test_returns_country_from_cache_on_second_call()
    {
        $mockService = $this->createMock(LookupBinInterface::class);

        $mockService->expects($this->once())
        ->method('getCountryCodeByBin')
            ->with('516793')
            ->willReturn(CountriesEnum::Lithuania);

        $proxy = new LookupBinProxy($mockService);

        $first = $proxy->getCountryCodeByBin('516793');
        $second = $proxy->getCountryCodeByBin('516793');

        $this->assertEquals(CountriesEnum::Lithuania, $first);
        $this->assertEquals(CountriesEnum::Lithuania, $second);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function test_fetches_country_if_not_cached()
    {
        $mockService = $this->createMock(LookupBinInterface::class);
        $mockService->expects($this->once())
            ->method('getCountryCodeByBin')
            ->with('45717360')
            ->willReturn(CountriesEnum::Denmark);

        $proxy = new LookupBinProxy($mockService);
        $country = $proxy->getCountryCodeByBin('45717360');
        $this->assertEquals(CountriesEnum::Denmark, $country);
    }

    /**
     * @throws Exception
     */
    public function test_throws_exception_from_source_service()
    {
        $mockService = $this->createMock(LookupBinInterface::class);
        $mockService->method('getCountryCodeByBin')
            ->willThrowException(new \Exception("Something went wrong"));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Something went wrong");

        $proxy = new LookupBinProxy($mockService);
        $proxy->getCountryCodeByBin('999999');
    }

    /**
     * @throws Exception
     */
    public function test_get_base_url_used_with_proxy()
    {
        $mockService = $this->createMock(LookupBinInterface::class);
        $mockService->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('https://example.com');

        $proxy = new LookupBinProxy($mockService);
        $url = $proxy->getBaseUrl();

        $this->assertEquals('https://example.com', $url);
    }
}