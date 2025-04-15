<?php

namespace Src\Parsers;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Src\Enums\CurrenciesEnum;
use Src\Parsers\Dto\TransactionDto;

class TransactionParser
{
    /**
     * @throws Exception
     */
    #[ArrayShape(['parsed' => "array", 'unhandled' => "array"])]
    public static function parse(string $filePath): array
    {
        $path = getcwd() . '/'. $filePath;

        if (!file_exists($path)) {
            throw new Exception("Invalid file path '$filePath'");
        }

        $transactions = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $parsed = [];
        $unhandled = [];

        foreach ($transactions as $transaction) {
            $transactionEncoded = json_decode($transaction, true);
            if (isset($transactionEncoded['bin']) &&
                is_numeric($transactionEncoded['bin']) &&
                isset($transactionEncoded['amount']) &&
                is_numeric($transactionEncoded['amount']) &&
                isset($transactionEncoded['currency']) &&
                CurrenciesEnum::tryFrom($transactionEncoded['currency'])) {
                $parsed[] = new TransactionDto(
                    $transactionEncoded['bin'],
                    $transactionEncoded['amount'],
                    $transactionEncoded['currency']
                );
            } else {
                $unhandled[] = new TransactionDto(
                    $transactionEncoded['bin'],
                    $transactionEncoded['amount'],
                    $transactionEncoded['currency']
                );
            }
        }

        if (count($parsed) === 0) {
            throw new Exception("Any transaction parsed in file '$filePath'");
        }

        return [
            'parsed' => $parsed,
            'unhandled' => $unhandled
        ];
    }
}