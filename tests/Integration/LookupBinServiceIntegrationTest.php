<?php

namespace Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\Http\CurlHttpClient;
use Src\Http\ScraperProxyApiService;
use Src\LookupBin\LookupBinService;

class LookupBinServiceIntegrationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_it_returns_country_code_from_real_api_with_proxy()
    {
        $service = new LookupBinService(new CurlHttpClient(), new ScraperProxyApiService());
        $result = $service->getCountryCodeByBin('516793');
        $this->assertEquals(CountriesEnum::Lithuania, $result);
    }
}