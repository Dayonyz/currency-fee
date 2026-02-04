<?php

namespace Src\Services\LookupBin;

use Exception;
use Src\Enums\CountriesEnum;
use Src\HttpCilents\Contracts\HttpClientInterface;
use Src\Services\LookupBin\Contracts\LookupBinInterface;
use Src\Services\ScraperHttpProxy\ScraperHttpProxyService;

class LookupBinService implements LookupBinInterface
{
    private ?ScraperHttpProxyService $proxy;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, ScraperHttpProxyService $proxy = null)
    {
        $this->httpClient = $httpClient;
        $this->proxy = $proxy;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = $_ENV['LOOKUP_BIN_ENDPOINT'];

        return $this->proxy ? $this->proxy->proxyUrlSource($baseUrl) : $baseUrl;
    }

    /**
     * @throws Exception
     */
    public function getCountryCodeByBin(string $bin): CountriesEnum
    {
        $url = $this->getBaseUrl() . '/' . $bin;

        try {
            $response = $this->httpClient->get($url);
        } catch (Exception $e) {
            if ($e->getCode() === 429) {
                throw new Exception("BIN lookup rate limit exceeded: {$bin}", 429, $e);
            }

            throw new Exception(
                "BIN lookup failed for {$bin}: {$e->getMessage()}",
                0,
                $e
            );
        }

        $alpha2 = $response['data']['country']['alpha2'] ?? null;

        if (!is_string($alpha2)) {
            throw new Exception("Invalid JSON response for BIN: {$bin}");
        }

        $country = CountriesEnum::tryFrom($alpha2);

        if ($country === null) {
            throw new Exception(
                "Unsupported country code '{$alpha2}' returned for BIN {$bin}"
            );
        }

        return $country;
    }

}