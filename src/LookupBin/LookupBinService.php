<?php

namespace Src\LookupBin;

use Exception;
use Src\Enums\CountriesEnum;
use Src\Http\Contracts\HttpClientInterface;
use Src\Http\ScraperProxyApiService;
use Src\LookupBin\Contracts\LookupBinInterface;

class LookupBinService implements LookupBinInterface
{
    private ?ScraperProxyApiService $proxy;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient,  ScraperProxyApiService $proxy = null)
    {
        $this->httpClient = $httpClient;
        $this->proxy = $proxy;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = 'https://lookup.binlist.net';

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
        } catch (Exception $exception) {
            if ($exception->getCode() === 429) {
                throw new Exception("Rate limit exceeded for BIN: {$bin}");
            } else {
                throw new Exception("Fetching data failed for BIN: {$bin} - {$exception->getMessage()}");
            }
        }

        if (!isset($response['data']['country']) || !isset($response['data']['country']['alpha2'])) {
            throw new Exception("Invalid JSON response for BIN: $bin");
        }

        if (!CountriesEnum::tryFrom($response['data']['country']['alpha2'])) {
            throw new Exception("Country alpha2 code id not defined in " . CountriesEnum::class);
        }

        return CountriesEnum::tryFrom($response['data']['country']['alpha2']);
    }
}