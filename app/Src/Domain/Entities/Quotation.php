<?php

namespace App\Src\Domain\Entities;

use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\ValueObjects\Money;

/**
 * Entidad que representa una cotización con inmutabilidad por versión.
 *
 * Encapsulates the quotation aggregate root with strict state machine transitions.
 * Once a quotation enters UNDER_REVIEW state, it becomes immutable to guarantee
 * audit trail integrity. Modifications require creating a new version.
 */
class Quotation
{
    /**
     * @param int|null            $id              Internal identifier, null for new entities
     * @param string              $quoteNumber     Unique human-readable quote identifier
     * @param int                 $prospectId      Associated prospect in the CRM
     * @param int|null            $planId          Selected plan, nullable for custom quotes
     * @param int                 $createdBy       User ID who created the quotation
     * @param QuotationStatus     $status          Current state machine state
     * @param Money               $subtotal        Net amount before taxes
     * @param Money               $tax             Tax amount applied
     * @param Money               $total           Gross amount (subtotal + tax)
     * @param \DateTimeInterface|null $validUntil  Expiration date for the quote validity
     * @param int                 $version         Version counter for immutability tracking
     * @param int|null            $parentId        Previous version ID for audit trail
     * @param string|null         $rejectionReason Reason if the quote was rejected
     * @param array               $items           Collection of line items
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
        private ?\DateTimeInterface $validUntil,
        private int $version = 1,
        private ?int $parentId = null,
        private ?string $rejectionReason = null,
        private array $items = [],
    ) {}

    /** Returns the internal database identifier. Null if not yet persisted. */
    public function id(): ?int { return $this->id; }

    /** Returns the unique human-readable quote number. */
    public function quoteNumber(): string { return $this->quoteNumber; }

    /** Returns the current state machine status of the quotation. */
    public function status(): QuotationStatus { return $this->status; }

    /** Returns the version number used for immutability and audit trail. */
    public function version(): int { return $this->version; }

    /** Returns the collection of line items associated with this quotation. */
    public function items(): array { return $this->items; }

    /**
     * Sends the quotation to the approval workflow.
     *
     * Regla de negocio: solo cotizaciones en borrador pueden enviarse a revisión.
     * Una vez enviada, la cotización no puede modificarse para garantizar la integridad
     * del proceso de aprobación.
     *
     * @throws \DomainException if the current status does not allow this transition
     */
    /** @throws \DomainException */
    public function sendForApproval(): void
    {
        if (!$this->status->canBeSentForApproval()) {
            throw new \DomainException(
                __('domain.quotation.immutable')
            );
        }
        $this->status = QuotationStatus::UNDER_REVIEW;
    }

    /**
     * Approves the quotation, moving it to the APPROVED terminal state.
     *
     * Regla de negocio: solo cotizaciones en revisión pueden aprobarse.
     * Una cotización aprobada está lista para ser convertida en contrato.
     *
     * @throws \DomainException if the current status does not allow this transition
     */
    /** @throws \DomainException */
    public function approve(): void
    {
        if (!$this->status->canBeApproved()) {
            throw new \DomainException(__('domain.quotation.must_be_under_review_to_approve'));
        }
        $this->status = QuotationStatus::APPROVED;
    }

    /**
     * Rejects the quotation with a mandatory reason.
     *
     * Regla de negocio: solo cotizaciones en revisión pueden rechazarse.
     * El motivo de rechazo es obligatorio para mantener la trazabilidad de la decisión.
     *
     * @param string $reason The reason why the quotation was rejected
     * @throws \DomainException if the current status does not allow this transition
     */
    /** @throws \DomainException */
    public function reject(string $reason): void
    {
        if (!$this->status->canBeRejected()) {
            throw new \DomainException(__('domain.quotation.must_be_under_review_to_reject'));
        }
        $this->status = QuotationStatus::REJECTED;
        $this->rejectionReason = $reason;
    }

    /**
     * Determines whether the quotation can still be modified.
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
