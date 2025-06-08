<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Src\Enums\CountriesEnum;
use Src\Services\Commission\CommissionCalculator;

class CommissionCalculatorTest extends TestCase
{
    public function testCommissionRateForNorway(): void
    {
        $rate = CommissionCalculator::getCommissionRateByCountry(CountriesEnum::Norway);
        $this->assertSame(0.03, $rate, 'Norway should have custom commission rate of 0.03');
    }

    public function testCommissionRateForEuCountries(): void
    {
        foreach (CountriesEnum::cases() as $country) {
            if ($country === CountriesEnum::Norway) {
                continue;
            }

            $expectedRate = $country->isEU() ? 0.01 : 0.02;

            $rate = CommissionCalculator::getCommissionRateByCountry($country);

            $this->assertSame(
                $expectedRate,
                $rate,
                sprintf(
                    'Failed asserting commission rate for %s (%s) - Expected: %.2f, Got: %.2f',
                    $country->name,
                    $country->value,
                    $expectedRate,
                    $rate
                )
            );
        }
    }
}