<?php

namespace App\Src\Domain\ValueObjects;

/**
 * Value Object inmutable para representar dinero con moneda.
 *
 * Encapsula valores monetarios con conocimiento de moneda para evitar
 * mezclar monedas en operaciones aritméticas. Todas las operaciones retornan
 * nuevas instancias para garantizar la inmutabilidad.
 */
class Money
{
    /**
     * @param float  $amount   El valor monetario numérico
     * @param string $currency Código de moneda ISO 4217, por defecto COP (Peso Colombiano)
     *
     * @throws \InvalidArgumentException si el monto es negativo
     */
    public function __construct(
        private float $amount,
        private string $currency = 'COP',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('domain.error.negative_amount');
        }
    }

    /** Retorna el monto numérico. */
    public function amount(): float
    {
        return $this->amount;
    }

    /** Retorna el código de moneda ISO 4217. */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Suma otra instancia de Money a esta, retornando una nueva instancia.
     *
     * @param Money $other El dinero a sumar
     * @return self Una nueva instancia de Money con el monto summed
     * @throws \InvalidArgumentException si las monedas no coinciden
     */
    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(__('domain.error.different_currencies'));
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Multiplica el monto por una cantidad dada, retornando una nueva instancia.
     * Útil para calcular totales de items de línea.
     *
     * @param int $quantity El multiplicador
     * @return self Una nueva instancia de Money con el monto multiplied
     */
    public function multiply(int $quantity): self
    {
        return new self($this->amount * $quantity, $this->currency);
    }

    /**
     * Formatea el valor monetario para visualización usando la convención locale colombiana.
     * Ejemplo de salida: .234.567,89
     */
    public function format(): string
    {
        return '$' . number_format($this->amount, 2, ',', '.');
    }
}
