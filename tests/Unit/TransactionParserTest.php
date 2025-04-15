<?php

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Parsers\TransactionParser;
use Src\Parsers\Dto\TransactionDto;
use Src\Enums\CurrenciesEnum;

class TransactionParserTest extends TestCase
{
    private string $validFile;
    private string $invalidFile;
    private string $completelyInvalidFile;
    private string $emptyFile;
    private string $nonExistentFile = 'nonexistent.txt';

    protected function setUp(): void
    {
        $this->validFile = 'tests/files/valid_transactions.txt';
        $this->invalidFile = 'tests/files/invalid_transactions.txt';
        $this->completelyInvalidFile = 'tests/files/completely_invalid_transactions.txt';
        $this->emptyFile = 'tests/files/empty_transactions.txt';

        if (!is_dir('tests/files')) {
            mkdir('tests/files', 0777, true);
        }

        file_put_contents($this->validFile, implode(PHP_EOL, [
            json_encode(['bin' => '45717360', 'amount' => 100.00, 'currency' => 'USD']),
            json_encode(['bin' => '516793', 'amount' => 50.50, 'currency' => 'EUR']),
        ]));

        file_put_contents($this->completelyInvalidFile, implode(PHP_EOL, [
            json_encode(['bin' => 'abc', 'amount' => 100.00, 'currency' => 'USD']),
            json_encode(['bin' => '45717360', 'amount' => 'xx', 'currency' => 'USD']),
            json_encode(['bin' => '45717360', 'amount' => 100.00, 'currency' => 'XX1']),
        ]));

        file_put_contents($this->invalidFile, implode(PHP_EOL, [
            json_encode(['bin' => 'abc', 'amount' => 100.00, 'currency' => 'USD']), // invalid bin
            json_encode(['bin' => '45717360', 'amount' => 'xx', 'currency' => 'USD']), // invalid amount
            json_encode(['bin' => '45717360', 'amount' => 100.00, 'currency' => 'XX1']), // invalid currency
            json_encode(['bin' => '516793', 'amount' => 50.50, 'currency' => 'EUR']),// one valid raw
        ]));

        file_put_contents($this->emptyFile, '');
    }

    protected function tearDown(): void
    {
        @unlink($this->validFile);
        @unlink($this->invalidFile);
        @unlink($this->completelyInvalidFile);
        @unlink($this->emptyFile);
        @rmdir('tests/files');
    }

    /**
     * @throws Exception
     */
    public function test_parses_valid_transactions_successfully()
    {
        $result = TransactionParser::parse($this->validFile);

        $this->assertCount(2, $result['parsed']);
        $this->assertEmpty($result['unhandled']);
        $this->assertContainsOnlyInstancesOf(TransactionDto::class, $result['parsed']);
        $this->assertEquals('45717360', $result['parsed'][0]->bin);
        $this->assertEquals(CurrenciesEnum::US_Dollar->value, $result['parsed'][0]->currency);
    }

    /**
     * @throws Exception
     */
    public function test_parses_invalid_transactions_partially()
    {
        $result = TransactionParser::parse($this->invalidFile);

        $this->assertCount(1, $result['parsed']);
        $this->assertCount(3, $result['unhandled']);
    }

    public function test_throws_exception_on_completely_invalid_data()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Any transaction parsed in file 'tests/files/completely_invalid_transactions.txt'");
        TransactionParser::parse($this->completelyInvalidFile);
    }

    public function test_throws_exception_on_nonexistent_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid file path '{$this->nonExistentFile}'");

        TransactionParser::parse($this->nonExistentFile);
    }

    public function test_throws_exception_on_empty_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Any transaction parsed in file 'tests/files/empty_transactions.txt'");
        TransactionParser::parse($this->emptyFile);
    }
}