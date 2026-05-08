<?php

namespace App\Src\Domain\ValueObjects;

/**
 * Value Object inmutable para representar dinero con moneda.
 *
 * Encapsulates monetary values with currency awareness to prevent
 * mixing currencies in arithmetic operations. All operations return
 * new instances to guarantee immutability.
 */
class Money
{
    /**
     * @param float  $amount   The numeric monetary value
     * @param string $currency ISO 4217 currency code, defaults to COP (Colombian Peso)
     *
     * @throws \InvalidArgumentException if amount is negative
     */
    public function __construct(
        private float $amount,
        private string $currency = 'COP',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException(__('domain.error.negative_amount'));
        }
    }

    /** Returns the numeric amount. */
    public function amount(): float
    {
        return $this->amount;
    }

    /** Returns the ISO 4217 currency code. */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Adds another Money instance to this one, returning a new instance.
     *
     * @param Money $other The money to add
     * @return self A new Money instance with the summed amount
     * @throws \InvalidArgumentException if currencies do not match
     */
    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(__('domain.error.different_currencies'));
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Multiplies the amount by a given quantity, returning a new instance.
     * Useful for calculating line-item totals.
     *
     * @param int $quantity The multiplier
     * @return self A new Money instance with the multiplied amount
     */
    public function multiply(int $quantity): self
    {
        return new self($this->amount * $quantity, $this->currency);
    }

    /**
     * Formats the monetary value for display using Colombian locale convention.
     * Example output: $1.234.567,89
     */
    public function format(): string
    {
        return '$' . number_format($this->amount, 2, ',', '.');
    }
}
