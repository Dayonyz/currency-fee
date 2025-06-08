<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\CurrenciesEnum;
use Src\Services\Parsers\Dto\TransactionDto;
use Src\Services\Parsers\TransactionFileParser;

class TransactionParserTest extends TestCase
{
    private string $testDir = 'tests/files/';
    private string $validFile;
    private string $invalidJsonFile;
    private string $invalidDataFile;
    private string $emptyFile;
    private string $nonExistentFile;

    protected function setUp(): void
    {
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }

        $this->validFile = $this->testDir . 'valid_transactions.txt';
        $this->invalidJsonFile = $this->testDir . 'invalid_json_transactions.txt';
        $this->invalidDataFile = $this->testDir . 'invalid_data_transactions.txt';
        $this->emptyFile = $this->testDir . 'empty_transactions.txt';
        $this->nonExistentFile = $this->testDir . 'nonexistent.txt';

        // Valid transactions
        file_put_contents($this->validFile, implode(PHP_EOL, [
            json_encode(['bin' => '45717360', 'amount' => 100.00, 'currency' => 'USD']),
            json_encode(['bin' => '516793', 'amount' => 50.50, 'currency' => 'EUR']),
        ]));

        // Invalid JSON line
        file_put_contents($this->invalidJsonFile, implode(PHP_EOL, [
            'invalid-json-line',
            json_encode(['bin' => '516793', 'amount' => 50.50, 'currency' => 'EUR']),
        ]));

        // Invalid data entries
        file_put_contents($this->invalidDataFile, implode(PHP_EOL, [
            json_encode(['bin' => 'abc', 'amount' => 100.00, 'currency' => 'USD']), // invalid bin
            json_encode(['bin' => '45717360', 'amount' => 'xx', 'currency' => 'USD']), // invalid amount
            json_encode(['bin' => '45717360', 'amount' => 100.00, 'currency' => 'XX1']), // invalid currency
            json_encode(['bin' => '516793', 'amount' => 50.50, 'currency' => 'EUR']), // valid
        ]));

        // Empty file
        file_put_contents($this->emptyFile, '');
    }

    protected function tearDown(): void
    {
        @unlink($this->validFile);
        @unlink($this->invalidJsonFile);
        @unlink($this->invalidDataFile);
        @unlink($this->emptyFile);
        @rmdir($this->testDir);
    }

    /**
     * @throws Exception
     */
    public function test_iterate_valid_transactions()
    {
        $generator = TransactionFileParser::iterate($this->validFile);
        $results = iterator_to_array($generator);

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(TransactionDto::class, $result);
        }

        $this->assertEquals('45717360', $results[0]->bin);
        $this->assertEquals(CurrenciesEnum::US_Dollar->value, $results[0]->currency);
    }

    /**
     * @throws Exception
     */
    public function test_iterate_invalid_json_line()
    {
        $generator = TransactionFileParser::iterate($this->invalidJsonFile);
        $results = iterator_to_array($generator);

        $this->assertCount(2, $results);
        $this->assertIsString($results[0]);
        $this->assertStringContainsString('Transaction skipped - Invalid json', $results[0]);
        $this->assertInstanceOf(TransactionDto::class, $results[1]);
    }

    /**
     * @throws Exception
     */
    public function test_iterate_invalid_data_entries()
    {
        $generator = TransactionFileParser::iterate($this->invalidDataFile);
        $results = iterator_to_array($generator);

        $this->assertCount(4, $results);
        $this->assertIsString($results[0]);
        $this->assertStringContainsString('Transaction skipped - Invalid entry values', $results[0]);
        $this->assertIsString($results[1]);
        $this->assertIsString($results[2]);
        $this->assertInstanceOf(TransactionDto::class, $results[3]);
    }

    /**
     * @throws Exception
     */
    public function test_iterate_empty_file()
    {
        $generator = TransactionFileParser::iterate($this->emptyFile);
        $results = iterator_to_array($generator);

        $this->assertCount(0, $results);
    }

    public function test_iterate_nonexistent_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid file path '{$this->nonExistentFile}'");

        iterator_to_array(TransactionFileParser::iterate($this->nonExistentFile));
    }
}