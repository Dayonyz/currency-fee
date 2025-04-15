<?php

use Src\Enums\CurrenciesEnum;
use Src\ExchangeRates\OpenExchangeRatesProxy;
use Src\LookupBin\LookupBinProxy;
use Src\Parsers\Dto\TransactionDto;
use Src\Parsers\TransactionParser;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseCurrency = CurrenciesEnum::Euro;
$binService = new LookupBinProxy();

$ratesService = new OpenExchangeRatesProxy();
$skippedMessage = "Transaction skipped - bin: {bin}, currency: {currency}', amount: {amount} - {exception}";
$handledMessage = "Transaction handled - commission: {commission}, bin: {bin}, currency: {currency}', amount: {amount}";

function ceilTo(float $number, int $precision = 2): float {
    $factor = pow(10, $precision);
    return ceil($number * $factor) / $factor;
}

try {
    $parserResponse = TransactionParser::parse($argv[1]);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

/**
 * @var TransactionDto $unhandled
 */
foreach ($parserResponse['unhandled'] as $unhandled)
{
    echo strtr(
            $skippedMessage,
            [
                '{bin}' => $unhandled->bin,
                '{currency}' => $unhandled->currency,
                '{amount}' => $unhandled->amount,
                '{exception}' => 'Can not be parsed, jsom data are incorrect'
            ]) . PHP_EOL;
}

/**
 * @var TransactionDto $transaction
 */
foreach ($parserResponse['parsed'] as $transaction) {
    try {
        $countryEnum = $binService->getCountryCodeByBin($transaction->bin);
    } catch (Exception $exception)
    {
        echo strtr(
            $skippedMessage,
            [
                '{bin}' => $transaction->bin,
                '{currency}' => $transaction->currency ,
                '{amount}' => $transaction->amount,
                '{exception}' => $exception->getMessage()
            ]) . PHP_EOL;
        continue;
    }

    try {
        $rate = $baseCurrency->value === $transaction->currency ? 1 : $ratesService->getCurrencyRateByDate(
            $transaction->currency,
            $baseCurrency->value ,
            new DateTime()
        );
        echo strtr(
                $handledMessage,
                [
                    '{commission}' => ceilTo($transaction->amount/$rate*$countryEnum->getCommissionRate(), 2),
                    '{bin}' => $transaction->bin,
                    '{currency}' => $transaction->currency ,
                    '{amount}' => $transaction->amount,
                ]) . PHP_EOL;
    } catch (Exception $exception) {
        echo strtr(
                $skippedMessage,
                [
                    '{bin}' => $transaction->bin,
                    '{currency}' => $transaction->currency ,
                    '{amount}' => $transaction->amount,
                    '{exception}' => $exception->getMessage() . ', currency rate is unavailable'
                ]) . PHP_EOL;
    }
}