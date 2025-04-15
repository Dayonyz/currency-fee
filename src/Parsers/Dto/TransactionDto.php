<?php

namespace Src\Parsers\Dto;

class TransactionDto
{
    public string $bin;
    public string $amount;
    public string $currency;

    public function __construct(string $bin, string $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }
}