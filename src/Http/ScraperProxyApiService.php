<?php

namespace Src\Http;

class ScraperProxyApiService
{
    private static function getBaseUrl() : string
    {
        return "https://api.scraperapi.com/?api_key=" . $_ENV['SCRAPER_API_KEY'] . "&url=";
    }

    public function proxyUrlSource(string $urlSource): string
    {
        if (!filter_var($urlSource, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Incorrect URL for proxying : $urlSource");
        }

        return static::getBaseUrl() . $urlSource;
    }
}