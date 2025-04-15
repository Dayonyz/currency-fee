<?php

namespace Src\LookupBin;

use Exception;
use Src\Enums\CountriesEnum;
use Src\Http\ScraperProxyApiService;
use Src\LookupBin\Contracts\LookupBinInterface;

class LookupBinService implements LookupBinInterface
{
    protected string $bin;
    private ?ScraperProxyApiService $scraper;

    public function __construct(ScraperProxyApiService $scraper = null)
    {
        $this->scraper = $scraper;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = 'https://lookup.binlist.net';

        return $this->scraper ? $this->scraper->proxyUrlSource($baseUrl) : $baseUrl;
    }

    /**
     * @throws Exception
     */
    public function getCountryCodeByBin(string $bin): CountriesEnum
    {
        $url = $this->getBaseUrl() . '/' . $bin;
        $this->bin = $bin;

        $data = $this->request($url);

        if (!isset($data['country']) || !isset($data['country']['alpha2'])) {
            throw new Exception("Invalid JSON response for BIN: $bin");
        }

        if (!CountriesEnum::tryFrom($data['country']['alpha2'])) {
            throw new Exception("Country alpha2 code id not defined in " . CountriesEnum::class);
        }

        return CountriesEnum::tryFrom($data['country']['alpha2']);
    }

    /**
     * @throws Exception
     */
    protected function request(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 429) {
            throw new Exception("Rate limit exceeded for BIN: {$this->bin}");
        }

        if ($response === false || $httpCode !== 200) {
            throw new Exception("Failed to fetch data for BIN: {$this->bin}. HTTP code: $httpCode");
        }

        return json_decode($response, true);
    }
}