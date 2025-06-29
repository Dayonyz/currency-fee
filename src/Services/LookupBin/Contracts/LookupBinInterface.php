<?php

namespace Src\Services\LookupBin\Contracts;

use Src\Enums\CountriesEnum;

interface LookupBinInterface
{
    public function getBaseUrl(): string;

    public function getCountryCodeByBin(string $bin): CountriesEnum;
}