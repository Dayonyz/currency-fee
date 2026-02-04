<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\HttpCilents\Contracts\HttpClientInterface;
use Src\Services\LookupBin\LookupBinService;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

class LookupBinServiceTest extends TestCase
{
    private HttpClientInterface $httpClientMock;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
    }

    public function testGetBaseUrlWithoutScraperReturnsDefaultUrl(): void
    {
        $service = new LookupBinService($this->httpClientMock);
        $this->assertEquals('https://lookup.binlist.net', $service->getBaseUrl());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetBaseUrlWithScraperReturnsProxiedUrl(): void
    {
        $scraperMock = $this->createMock(ScraperHttpProxyService::class);
        $scraperMock->method('proxyUrlSource')
            ->with('https://lookup.binlist.net')
            ->willReturn('https://proxy.example.com/lookup.binlist.net');

        $service = new LookupBinService($this->httpClientMock, $scraperMock);
        $this->assertEquals('https://proxy.example.com/lookup.binlist.net', $service->getBaseUrl());
    }

    /**
     * @throws Exception
     */
    public function testGetCountryCodeByBinReturnsValidCountry(): void
    {
        $bin = '45717360';
        $expectedAlpha2 = 'LT';

        $this->httpClientMock->method('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->willReturn([
                'data' => [
                    'country' => [
                        'alpha2' => $expectedAlpha2
                    ]
                ],
                'code' => 200,
            ]);

        $service = new LookupBinService($this->httpClientMock);
        $result = $service->getCountryCodeByBin($bin);

        $this->assertInstanceOf(CountriesEnum::class, $result);
        $this->assertEquals($expectedAlpha2, $result->value);
    }

    public function testGetCountryCodeByBinThrowsExceptionOnInvalidResponse(): void
    {
        $bin = '123456';

        $this->httpClientMock->method('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->willReturn([
                'data' => [],
                'code' => 200,
            ]);

        $service = new LookupBinService($this->httpClientMock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid JSON response for BIN: {$bin}");

        $service->getCountryCodeByBin($bin);
    }

    public function testGetCountryCodeByBinThrowsExceptionOnUnknownCountryCode(): void
    {
        $bin = '123456';
        $invalidCountryCode = 'XX';

        $this->httpClientMock->method('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->willReturn([
                'data' => [
                    'country' => [
                        'alpha2' => $invalidCountryCode
                    ]
                ],
                'code' => 200,
            ]);

        $service = new LookupBinService($this->httpClientMock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unsupported country code '{$invalidCountryCode}' returned for BIN {$bin}");

        $service->getCountryCodeByBin($bin);
    }

    public function testGetCountryCodeByBinThrowsExceptionOnRateLimit(): void
    {
        $bin = '123456';

        $this->httpClientMock->method('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->willThrowException(new Exception("Too Many Requests", 429));

        $service = new LookupBinService($this->httpClientMock);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("BIN lookup rate limit exceeded: {$bin}");

        $service->getCountryCodeByBin($bin);
    }

    public function testGetCountryCodeByBinThrowsExceptionOnGenericHttpError(): void
    {
        $bin = '123456';

        $this->httpClientMock->method('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->willThrowException(new Exception("Something went wrong", 500));

        $service = new LookupBinService($this->httpClientMock);
        $this->expectException(Exception::class);

        $this->expectExceptionMessage("BIN lookup failed for {$bin}: Something went wrong");

        $service->getCountryCodeByBin($bin);
    }
}