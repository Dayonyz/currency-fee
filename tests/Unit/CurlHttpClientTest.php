<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\HttpCilents\CurlHttpClient;

class CurlHttpClientTest extends TestCase
{
    private CurlHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new CurlHttpClient();
    }

    /**
     * @throws Exception
     */
    public function testGetReturnsValidResponseStructure(): void
    {
        $url = 'https://jsonplaceholder.typicode.com/todos/1';
        $response = $this->client->get($url);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('code', $response);
        $this->assertIsArray($response['data']);
        $this->assertEquals(200, $response['code']);
    }

    public function testGetThrowsExceptionOnInvaliUrl(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Invalid URL provided:/');

        $this->client->get('not-a-valid-url');
    }

    public function testGetThrowsExceptionOnUnreachableUrl(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Request error:/');

        $this->client->get('http://nonexistent.example.com');
    }
}
