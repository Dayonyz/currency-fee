<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\LookupBin\LookupBinService;
use Src\Enums\CountriesEnum;

class LookupBinServiceTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_it_returns_country_code_with_mocked_request()
    {
        $mockedService = $this->getMockBuilder(LookupBinService::class)
            ->onlyMethods(['request'])
            ->getMock();

        $mockedService->expects($this->once())
            ->method('request')
            ->with('https://lookup.binlist.net/516793')
            ->willReturn([
                'country' => ['alpha2' => 'LT']
            ]);

        $result = $mockedService->getCountryCodeByBin('516793');

        $this->assertEquals(CountriesEnum::Lithuania, $result);
    }

    public function test_it_throws_exception_when_country_key_missing()
    {
        $bin = '41417355';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid JSON response for BIN: $bin");

        $stub = $this->getMockBuilder(LookupBinService::class)
            ->onlyMethods(['request'])
            ->getMock();

        $stub->method('request')
            ->willReturn([]);

        $stub->getCountryCodeByBin($bin);
    }

    public function test_it_throws_exception_when_alpha2_missing()
    {
        $bin = '41417355';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid JSON response for BIN: $bin");

        $stub = $this->getMockBuilder(LookupBinService::class)
            ->onlyMethods(['request'])
            ->getMock();

        $stub->method('request')
            ->willReturn([
                'country' => []
            ]);

        $stub->getCountryCodeByBin($bin);
    }

    public function test_it_throws_exception_on_unknown_alpha2()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Country alpha2 code id not defined in ' . CountriesEnum::class);

        $stub = $this->getMockBuilder(LookupBinService::class)
            ->onlyMethods(['request'])
            ->getMock();

        $stub->method('request')
            ->willReturn([
                'country' => ['alpha2' => 'XX']
            ]);

        $stub->getCountryCodeByBin('11111');
    }
}