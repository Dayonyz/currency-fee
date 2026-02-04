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
    public function testReturnsCountryCodeFromRealApiWithProxy()
    {
        $service = new LookupBinService(new CurlHttpClient(), new ScraperHttpProxyService());
        $result = $service->getCountryCodeByBin('516793');

        $this->assertSame(CountriesEnum::Lithuania, $result);
    }
}