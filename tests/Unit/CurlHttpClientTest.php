<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Http\CurlHttpClient;

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
    public function test_get_returns_valid_response_structure(): void
    {
        $url = 'https://jsonplaceholder.typicode.com/todos/1';
        $response = $this->client->get($url);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('code', $response);
        $this->assertIsArray($response['data']);
        $this->assertEquals(200, $response['code']);
    }

    public function test_get_throws_exception_on_invalid_url(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Invalid URL provided:/');

        $this->client->get('not-a-valid-url');
    }

    public function test_get_throws_exception_on_unreachable_url(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Request error:/');

        $this->client->get('http://nonexistent.example.com');
    }
}
