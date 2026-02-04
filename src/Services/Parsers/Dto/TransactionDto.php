<?php

namespace Src\Services\Parsers\Dto;

use Src\Enums\CurrenciesEnum;

class TransactionDto
{
    public string $bin;
    public string $amount;
    public CurrenciesEnum $currency;

    public function __construct(string $bin, string $amount, CurrenciesEnum $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }
}