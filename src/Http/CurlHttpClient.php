<?php

namespace Src\Http;

use Exception;
use Src\Http\Contracts\HttpClientInterface;

class CurlHttpClient implements HttpClientInterface
{
    /**
     * @throws Exception
     */
    public function get(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL provided: $url");
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception("Failed to initialize cURL session.");
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: $error");
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return [
            'data' => json_decode($response, true),
            'code' => $httpCode
        ];
    }
}