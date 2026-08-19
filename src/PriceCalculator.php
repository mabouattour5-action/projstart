<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Computes cart totals: subtotal, discounts and VAT.
 */
final class PriceCalculator
{
    public const VAT_RATE = 0.20;

    /** @var array<string, float> discount code => rate off */
    private const DISCOUNTS = [
        'WELCOME10' => 0.10,
        'SUMMER25'  => 0.25,
        'HALFOFF'   => 0.50,
    ];

    /**
     * @param list<array{price: float, quantity: int}> $items
     */
    public function subtotal(array $items): float
    {
        $sum = 0.0;

        foreach ($items as $item) {
            if ($item['price'] < 0) {
                throw new InvalidArgumentException('Price cannot be negative.');
            }

            if ($item['quantity'] < 1) {
                throw new InvalidArgumentException('Quantity must be at least 1.');
            }

            $sum += $item['price'] * $item['quantity'];
        }

        return $this->round($sum);
    }

    public function applyDiscount(float $amount, string $code): float
    {
        $code = strtoupper(trim($code));

        if (!isset(self::DISCOUNTS[$code])) {
            throw new InvalidArgumentException(sprintf('Unknown discount code "%s".', $code));
        }

        return $this->round($amount * (1 - self::DISCOUNTS[$code]));
    }

    public function addVat(float $amount): float
    {
        return $this->round($amount * (1 + self::VAT_RATE));
    }

    /**
     * @param list<array{price: float, quantity: int}> $items
     */
    public function total(array $items, ?string $discountCode = null): float
    {
        $amount = $this->subtotal($items);

        if ($discountCode !== null && $discountCode !== '') {
            $amount = $this->applyDiscount($amount, $discountCode);
        }

        return $this->addVat($amount);
    }

    private function round(float $amount): float
    {
        return round($amount, 2);
    }
}
