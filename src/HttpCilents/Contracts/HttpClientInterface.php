<?php

namespace Src\HttpCilents\Contracts;

interface HttpClientInterface
{
    public function get(string $url): array;
}