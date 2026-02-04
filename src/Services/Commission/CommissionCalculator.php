<?php

namespace Src\Services\Commission;

use Src\Enums\CountriesEnum;

class CommissionCalculator
{
    protected function _getCommissionRateByCountry(CountriesEnum $country): float
    {
        return match ($country) {
            //Just for example we can use custom commission for any country
            // (does not meet business requirements in Readme.md)
            CountriesEnum::Norway => 0.03,

            default => $country->isEU() ? 0.01 : 0.02,
        };
    }

    public static function getCommissionRateByCountry(CountriesEnum $country): float
    {
        $obj = new static();

        return $obj->_getCommissionRateByCountry($country);
    }
}