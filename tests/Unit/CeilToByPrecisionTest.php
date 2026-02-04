<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CeilToByPrecisionTest extends TestCase
{
    public function testDefaultPrecision(): void
    {
        $this->assertSame(1.24, ceilToByPrecision(1.231));
        $this->assertSame(1.23, ceilToByPrecision(1.23));
        $this->assertSame(1.24, ceilToByPrecision(1.2301));
    }

    public function testCustomPrecision(): void
    {
        $this->assertSame(1.3, ceilToByPrecision(1.21, 1));
        $this->assertSame(1.21, ceilToByPrecision(1.201, 2));
        $this->assertSame(2.0, ceilToByPrecision(1.0001, 0));
    }

    public function testNegativeNumbers(): void
    {
        // ВАЖНО: ceil для отрицательных чисел ведёт себя "в сторону нуля"
        $this->assertSame(-1.23, ceilToByPrecision(-1.234));
        $this->assertSame(-1.2, ceilToByPrecision(-1.21, 1));
    }

    public function testAlreadyRounded(): void
    {
        $this->assertSame(5.55, ceilToByPrecision(5.55, 2));
    }

    public function testEdgeCases(): void
    {
        $this->assertSame(0.0, ceilToByPrecision(0.0));
        $this->assertSame(10.0, ceilToByPrecision(9.999, 0));
    }
}