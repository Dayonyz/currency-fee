<?php

namespace Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\LookupBin\LookupBinService;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
class LookupBinServiceIntegrationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testReturnsCountryCodeFromRealApi(): void
    {
        $service = new LookupBinService(
            new CurlHttpClient()
        );

        $country = $service->getCountryCodeByBin('516793');

        $this->assertSame(CountriesEnum::Lithuania, $country);
    }
}

