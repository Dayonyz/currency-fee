<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Src\Http\ScraperProxyApiService;

class ScraperProxyApiServiceTest extends TestCase
{
    private ScraperProxyApiService $proxyApiService;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->proxyApiService = new ScraperProxyApiService();
    }

    protected function setUp(): void
    {
        $_ENV['SCRAPER_API_KEY'] = 'test_key';
    }

    public function test_proxy_url_source_returns_correct_url()
    {
        $originalUrl = 'https://example.com';
        $expectedUrl = 'https://api.scraperapi.com/?api_key=test_key&url=' . $originalUrl;

        $result = $this->proxyApiService->proxyUrlSource($originalUrl);

        $this->assertEquals($expectedUrl, $result);
    }

    public function test_proxy_url_source_with_empty_url()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect URL for proxying');

        $this->proxyApiService->proxyUrlSource('');
    }

    public function test_proxy_url_source_with_invalid_url()
    {
        $invalidUrl = 'not-a-valid-url';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Incorrect URL for proxying : $invalidUrl");

        $this->proxyApiService->proxyUrlSource($invalidUrl);
    }

    public function test_proxy_url_source_with_special_characters()
    {
        $originalUrl = 'https://example.com/path?query=param&another=value';
        $expectedUrl = 'https://api.scraperapi.com/?api_key=test_key&url=' . $originalUrl;

        $result = $this->proxyApiService->proxyUrlSource($originalUrl);

        $this->assertEquals($expectedUrl, $result);
    }
}