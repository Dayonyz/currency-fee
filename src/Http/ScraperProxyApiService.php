<?php

namespace Src\Http;

class ScraperProxyApiService
{
    private static function getFullUrl() : string
    {
        return "https://api.scraperapi.com/?api_key=" . $_ENV['SCRAPER_API_KEY'] . "&url=";
    }

    public function proxyUrlSource(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Incorrect URL for proxying : $url");
        }

        return static::getFullUrl() . $url;
    }
}