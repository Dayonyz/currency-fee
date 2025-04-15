<?php

namespace Src\Http\Contracts;

interface HttpClientInterface
{
    public function get(string $url): array;
}