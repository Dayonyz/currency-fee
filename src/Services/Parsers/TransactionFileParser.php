<?php

namespace Src\Services\Parsers;

use Exception;
use Generator;
use Src\Enums\CurrenciesEnum;
use Src\Services\Parsers\Dto\TransactionDto;

class TransactionFileParser
{
    /**
     * @throws Exception
     */
    public static function iterate(string $filePath): Generator
    {
        $path = getcwd() . '/'. $filePath;

        if (!file_exists($path)) {
            throw new Exception("Invalid file path '$filePath'");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file '$filePath'");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $transactionEncoded = json_decode($line, true);

                if (!$transactionEncoded) {
                    yield "Transaction skipped - Invalid json '$line'" . PHP_EOL;
                    continue;
                }

                yield self::validateEntry($transactionEncoded);
            }
        } finally {
            fclose($handle);
        }
    }

    public static function validateEntry(array $entry): TransactionDto | string
    {
        if (isset($entry['bin']) &&
            is_numeric($entry['bin']) &&
            isset($entry['amount']) &&
            is_numeric($entry['amount']) &&
            isset($entry['currency']) &&
            CurrenciesEnum::tryFrom($entry['currency'])) {
            return new TransactionDto(
                $entry['bin'],
                $entry['amount'],
                $entry['currency']
            );
        } else {
            return "Transaction skipped - Invalid entry values: " . json_encode($entry) . PHP_EOL;
        }
    }
}