<?php

declare(strict_types=1);

namespace App\Tests;

use App\PriceCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    public function testSubtotalOfAnEmptyCartIsZero(): void
    {
        self::assertSame(0.0, $this->calculator->subtotal([]));
    }

    public function testSubtotalMultipliesPriceByQuantity(): void
    {
        $items = [
            ['price' => 10.00, 'quantity' => 2],
            ['price' => 4.50,  'quantity' => 3],
        ];

        self::assertSame(33.50, $this->calculator->subtotal($items));
    }

    public function testSubtotalRejectsNegativePrices(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->subtotal([['price' => -1.00, 'quantity' => 1]]);
    }

    public function testSubtotalRejectsZeroQuantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->subtotal([['price' => 10.00, 'quantity' => 0]]);
    }

    public function testApplyDiscountReducesTheAmount(): void
    {
        self::assertSame(90.00, $this->calculator->applyDiscount(100.00, 'WELCOME10'));
    }

    public function testDiscountCodesAreCaseInsensitive(): void
    {
        self::assertSame(50.00, $this->calculator->applyDiscount(100.00, 'halfoff'));
    }

    public function testApplyDiscountRejectsUnknownCodes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->applyDiscount(100.00, 'NOPE');
    }

    public function testAddVatAppliesTwentyPercent(): void
    {
        self::assertSame(120.00, $this->calculator->addVat(100.00));
    }

    public function testTotalCombinesSubtotalDiscountAndVat(): void
    {
        $items = [['price' => 50.00, 'quantity' => 2]];

        // 100.00 subtotal -> 75.00 after SUMMER25 -> 90.00 with VAT
        self::assertSame(90.00, $this->calculator->total($items, 'SUMMER25'));
    }

    public function testTotalWorksWithoutADiscountCode(): void
    {
        $items = [['price' => 19.99, 'quantity' => 1]];

        self::assertSame(23.99, $this->calculator->total($items));
    }
}
