<?php
namespace App\Src\Domain\Entities;

use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\ValueObjects\Money;

/**
 * Entidad que representa una cotización con inmutabilidad por versión.
 *
 * Encapsula la raíz del agregado de cotización con transiciones estrictas de máquina de estados.
 * Una vez que una cotización entra en estado UNDER_REVIEW, se vuelve inmutable para garantizar
 * la integridad del registro de auditoría. Las modificaciones requieren crear una nueva versión.
 */
class Quotation
{
    /**
     * @param int|null            $id              Identificador interno, null para nuevas entidades
     * @param string              $quoteNumber     Identificador único legible de cotización
     * @param int                 $prospectId      Prospecto asociado en el CRM
     * @param int|null            $planId          Plan seleccionado, nullable para cotizaciones personalizadas
     * @param int                 $createdBy       ID del usuario que creó la cotización
     * @param QuotationStatus     $status          Estado actual de la máquina de estados
     * @param Money               $subtotal        Monto neto antes de impuestos
     * @param Money               $tax             Monto de impuesto aplicado
     * @param Money               $total           Monto bruto (subtotal + impuesto)
     * @param \DateTimeInterface|null $validUntil  Fecha de vencimiento de la cotización
     * @param int                 $version         Contador de versión para seguimiento de inmutabilidad
     * @param int|null            $parentId        ID de versión anterior para registro de auditoría
     * @param string|null         $rejectionReason Motivo si la cotización fue rechazada
     * @param array               $items           Colección de items de línea
     */
    public function __construct(
        private ?int $id,
        private string $quoteNumber,
        private int $prospectId,
        private ?int $planId,
        private int $createdBy,
        private QuotationStatus $status,
        private Money $subtotal,
        private Money $tax,
        private Money $total,
        private  ? \DateTimeInterface $validUntil,
        private int $version = 1,
        private ?int $parentId = null,
        private ?string $rejectionReason = null,
        private array $items = [],
    ) {}

    /** Retorna el identificador interno de la base de datos. Null si aún no se ha persistido. */
    public function id(): ?int
    {
        return $this->id;
    }

    /** Retorna el número de cotización único legible. */
    public function quoteNumber(): string
    {
        return $this->quoteNumber;
    }

    /** Retorna el estado actual de la máquina de estados de la cotización. */
    public function status(): QuotationStatus
    {
        return $this->status;
    }

    /** Retorna el número de versión usado para inmutabilidad y registro de auditoría. */
    public function version(): int
    {
        return $this->version;
    }

    /** Retorna la colección de items de línea asociados a esta cotización. */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Envía la cotización al flujo de aprobación.
     *
     * Regla de negocio: solo cotizaciones en borrador pueden enviarse a revisión.
     * Una vez enviada, la cotización no puede modificarse para garantizar la integridad
     * del proceso de aprobación.
     *
     * @throws \DomainException si el estado actual no permite esta transición
     */
    /** @throws \DomainException */
    public function sendForApproval(): void
    {
        if (! $this->status->canBeSentForApproval()) {
            throw new \DomainException(
                'domain.quotation.immutable'
            );
        }
        $this->status = QuotationStatus::UNDER_REVIEW;
    }

    /**
     * Aprueba la cotización, moviéndola al estado terminal APPROVED.
     *
     * Regla de negocio: solo cotizaciones en revisión pueden aprobarse.
     * Una cotización aprobada está lista para ser convertida en contrato.
     *
     * @throws \DomainException si el estado actual no permite esta transición
     */
    /** @throws \DomainException */
    public function approve(): void
    {
        if (! $this->status->canBeApproved()) {
            throw new \DomainException('domain.quotation.must_be_under_review_to_approve');
        }
        $this->status = QuotationStatus::APPROVED;
    }

    /** @throws \DomainException */
    public function reject(string $reason): void
    {
        if (! $this->status->canBeRejected()) {
            throw new \DomainException('domain.quotation.must_be_under_review_to_reject');
        }
        $this->status          = QuotationStatus::REJECTED;
        $this->rejectionReason = $reason;
    }

    /**
     * Determina si la cotización aún puede ser modificada.
     *
     * Regla de auditoría: cotizaciones emitidas son inmutables.
     * Solo las cotizaciones en estado DRAFT pueden ser modificadas.
     * Para cambiar una cotización en revisión o aprobada, debe crearse una nueva versión.
     */
    public function canBeModified(): bool
    {
        return $this->status->isEditable();
    }
}
