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
    foreach (TransactionParser::iterate($argv[1]) as $transactionMessage) {
        if (is_string($transactionMessage)) {
            echo $transactionMessage;
        }
        if ($transactionMessage instanceof TransactionDto) {
            try {
                $countryEnum = $binService->getCountryCodeByBin($transactionMessage->bin);
            } catch (Exception $exception) {
                echo strtr(
                        $skippedMessage,
                        [
                            '{bin}' => $transactionMessage->bin,
                            '{currency}' => $transactionMessage->currency ,
                            '{amount}' => $transactionMessage->amount,
                            '{exception}' => $exception->getMessage()
                        ]) . PHP_EOL;
                continue;
            }

            try {
                $rate = $baseCurrency->value === $transactionMessage->currency ? 1 : $ratesService->getCurrencyRateByDate(
                    $transactionMessage->currency,
                    $baseCurrency->value ,
                    new DateTime()
                );
                echo strtr(
                        $handledMessage,
                        [
                            '{commission}' => ceilTo($transactionMessage->amount/$rate*$countryEnum->getCommissionRate(), 2),
                            '{bin}' => $transactionMessage->bin,
                            '{currency}' => $transactionMessage->currency ,
                            '{amount}' => $transactionMessage->amount,
                        ]) . PHP_EOL;
            } catch (Exception $exception) {
                echo strtr(
                        $skippedMessage,
                        [
                            '{bin}' => $transactionMessage->bin,
                            '{currency}' => $transactionMessage->currency ,
                            '{amount}' => $transactionMessage->amount,
                            '{exception}' => $exception->getMessage() . ', currency rate is unavailable'
                        ]) . PHP_EOL;
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}