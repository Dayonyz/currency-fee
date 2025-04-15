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

        curl_close($ch);
        return json_decode($response, true);
    }
}