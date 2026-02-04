<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

class ScraperProxyApiServiceTest extends TestCase
{
    private ScraperHttpProxyService $proxyApiService;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->proxyApiService = new ScraperHttpProxyService();
    }

    protected function setUp(): void
    {
        $_ENV['SCRAPER_API_KEY'] = 'test_key';
    }

    public function testProxyUrlSourceReturnsCorrectUrl()
    {
        $originalUrl = 'https://example.com';
        $expectedUrl = "{$_ENV['SCRAPER_API_ENDPOINT']}/?api_key={$_ENV['SCRAPER_API_KEY']}&url=$originalUrl";

        $result = $this->proxyApiService->proxyUrlSource($originalUrl);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testProxyUrlSourceWithEmptyUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect URL for proxying');

        $this->proxyApiService->proxyUrlSource('');
    }

    public function testProxyUrlSourceWithInvalidUrl()
    {
        $invalidUrl = 'not-a-valid-url';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Incorrect URL for proxying : $invalidUrl");

        $this->proxyApiService->proxyUrlSource($invalidUrl);
    }

    public function testProxyUrlSourceWithSpecialCharacters()
    {
        $originalUrl = 'https://example.com/path?query=param&another=value';
        $expectedUrl = "{$_ENV['SCRAPER_API_ENDPOINT']}/?api_key={$_ENV['SCRAPER_API_KEY']}&url=$originalUrl";

        $result = $this->proxyApiService->proxyUrlSource($originalUrl);

        $this->assertEquals($expectedUrl, $result);
    }
}