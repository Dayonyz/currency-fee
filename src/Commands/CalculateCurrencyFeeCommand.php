<?php

namespace Src\Commands;

use DateTime;
use Exception;
use Src\Enums\CurrenciesEnum;
use Src\Services\Commission\CommissionCalculator;
use Src\Services\ExchangeRates\Dto\ExchangeRateResult;
use Src\Services\ExchangeRates\FallbackOpenExchangeRatesService;
use Src\Services\LookupBin\LookupBinProxy;
use Src\Services\Parsers\Dto\TransactionDto;
use Src\Services\Parsers\TransactionFileParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateCurrencyFeeCommand extends Command
{
    protected static $defaultName = 'app:calculate-fees';

    protected function configure(): void
    {
        $this->setDescription('Calculate commission fees from input file')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Input file name (e.g. input.txt)'
            );
    }

    private function formatRateInfo(ExchangeRateResult $result): string
    {
        return $result->isApproximate
            ? '⚠ approximate rate (check .env credentials)'
            : 'real rate';
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        if (! is_file($file)) {
            $output->writeln("<error>File not found: {$file}</error>");
            return Command::FAILURE;
        }

        $baseCurrency = CurrenciesEnum::Euro;
        $binService   = new LookupBinProxy();
        $ratesService = new FallbackOpenExchangeRatesService();

        foreach (TransactionFileParser::iterate($file) as $message) {

            if (is_string($message)) {
                $output->writeln($message);
                continue;
            }

            if (! $message instanceof TransactionDto) {
                continue;
            }

            try {
                $country = $binService->getCountryCodeByBin($message->bin);
                $rateResponse = $ratesService->getCurrencyRateByDate(
                    $message->currency,
                    $baseCurrency,
                    new DateTime()
                );

                $commissionRate = CommissionCalculator::getCommissionRateByCountry($country);

                $commission = ceilToByPrecision(
                    $message->amount / $rateResponse->rate * $commissionRate,
                    2
                );

                $output->writeln(sprintf(
                    "Handled | commission: %.2f | bin: %s | currency: %s | amount: %.2f | %s",
                    $commission,
                    $message->bin,
                    $message->currency->value,
                    $message->amount,
                    $this->formatRateInfo($rateResponse)
                ));

            } catch (Exception $e) {
                $output->writeln(sprintf(
                    "<comment>Skipped</comment> | bin: %s | currency: %s | amount: %.2f | %s",
                    $message->bin,
                    $message->currency->value,
                    $message->amount,
                    $e->getMessage()
                ));
            }
        }

        return Command::SUCCESS;
    }
}
