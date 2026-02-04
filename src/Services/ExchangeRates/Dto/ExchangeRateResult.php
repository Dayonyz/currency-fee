<?php

namespace Src\Services\ExchangeRates\Dto;

class ExchangeRateResult
{
    public function __construct(
        public readonly float $rate,
        public readonly bool $isApproximate
    ) {}
}