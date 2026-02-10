<?php

namespace Src\Services\LookupBin;

use Exception;
use Src\Enums\CountriesEnum;
use Src\HttpCilents\CurlHttpClient;
use Src\Services\LookupBin\Contracts\LookupBinInterface;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

class LookupBinProxy implements LookupBinInterface
{
    private LookupBinInterface $sourceService;
    private array $cache;

    public function __construct(?LookupBinInterface $sourceService = null)
    {
        $this->sourceService = $sourceService ? : new LookupBinService(
            new CurlHttpClient(),
            new ScraperHttpProxyService()
        );
        $this->cache = [];
    }

    public function getBaseUrl(): string
    {
        return $this->sourceService->getBaseUrl();
    }

    /**
     * @throws Exception
     */
    public function getCountryCodeByBin(string $bin): CountriesEnum
    {
        if (isset($this->cache[$bin])) {
            return CountriesEnum::tryFrom($this->cache[$bin]);
        } else {
            $countryEnum = $this->sourceService->getCountryCodeByBin($bin);
            $this->cache[$bin] = $countryEnum->value;
        }

        return $countryEnum;
    }

    public function reset(): void
    {
        $this->cache = [];
    }
}