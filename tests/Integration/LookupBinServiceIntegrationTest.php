<?php

namespace Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\LookupBin\LookupBinService;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

class LookupBinServiceIntegrationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_it_returns_country_code_from_real_api_with_proxy()
    {
        $service = new LookupBinService(new CurlHttpClient(), new ScraperHttpProxyService());
        $result = $service->getCountryCodeByBin('516793');
        var_dump($result);
        $this->assertEquals(CountriesEnum::Lithuania, $result);
    }
}