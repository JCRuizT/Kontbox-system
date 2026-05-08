<?php

namespace App\Src\Domain\Entities;

class Invoice
{
    public function __construct(
        private ?int $id,
        private string $invoiceNumber,
        private int $contractId,
        private float $amount,
        private \DateTimeInterface $issuedDate,
        private string $status,
        private ?string $notes,
        private int $createdBy,
    ) {}
    /**
     * Retorna el identificador interno de la factura.
     */


    public function id(): ?int
{
    return $this->id;
}
    /**
     * Retorna el n\u00famero \u00fanico de la factura.
     */

    public function invoiceNumber(): string
{
    return $this->invoiceNumber;
}
    /**
     * Retorna el ID del contrato asociado.
     */

    public function contractId(): int
{
    return $this->contractId;
}
    /**
     * Retorna el monto de la factura.
     */

    public function amount(): float
{
    return $this->amount;
}
    /**
     * Retorna la fecha de emisi\u00f3n de la factura.
     */

    public function issuedDate(): \DateTimeInterface
{
    return $this->issuedDate;
}
    /**
     * Retorna el estado actual de la factura.
     */

    public function status(): string
{
    return $this->status;
}
    /**
     * Retorna las notas adicionales, o null si no hay.
     */

    public function notes(): ?string
{
    return $this->notes;
}
    /**
     * Retorna el ID del usuario que cre\u00f3 la factura.
     */

    public function createdBy(): int
{
    return $this->createdBy;
}
}
