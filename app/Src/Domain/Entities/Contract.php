<?php

namespace App\Src\Domain\Entities;

use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\ValueObjects\SignedPdf;

/**
 * Entidad que representa un contrato legal derivado de una cotización aprobada.
 *
 * Encapsulates the contract aggregate root with strict document and activation lifecycle.
 * A contract requires a signed PDF to be uploaded before it can be activated.
 * Security blocks prevent activation without a physically stored document.
 */
class Contract
{
    /**
     * @param int|null                 $id                 Internal identifier, null for new entities
     * @param string                   $contractNumber     Unique human-readable contract identifier
     * @param int                      $quotationId        Source quotation that originated this contract
     * @param int                      $approvedBy         User ID who approved the contract
     * @param ContractStatus           $status             Current state machine state
     * @param SignedPdf|null           $signedPdf          Signed PDF document uploaded to the platform
     * @param \DateTimeInterface|null  $startDate          Contract effective start date
     * @param \DateTimeInterface|null  $endDate            Contract expiration date
     * @param float                    $totalAmount        Monetary value of the contract
     * @param \DateTimeInterface|null  $activatedAt        Timestamp when the contract was activated
     * @param \DateTimeInterface|null  $cancelledAt        Timestamp when the contract was cancelled
     * @param string|null              $cancellationReason Reason for cancellation
     */
    public function __construct(
        private ?int $id,
        private string $contractNumber,
        private int $quotationId,
        private int $approvedBy,
        private ContractStatus $status,
        private ?SignedPdf $signedPdf = null,
        private ?\DateTimeInterface $startDate = null,
        private ?\DateTimeInterface $endDate = null,
        private float $totalAmount = 0,
        private ?\DateTimeInterface $activatedAt = null,
        private ?\DateTimeInterface $cancelledAt = null,
        private ?string $cancellationReason = null,
    ) {}

    /** Returns the internal database identifier. Null if not yet persisted. */
    public function id(): ?int { return $this->id; }

    /** Returns the unique human-readable contract number. */
    public function contractNumber(): string { return $this->contractNumber; }

    /** Returns the ID of the source quotation that originated this contract. */
    public function quotationId(): int { return $this->quotationId; }

    /** Returns the user ID who approved the contract. */
    public function approvedBy(): int { return $this->approvedBy; }

    /** Returns the current state machine status of the contract. */
    public function status(): ContractStatus { return $this->status; }

    /** Returns the signed PDF document, or null if not yet uploaded. */
    public function signedPdf(): ?SignedPdf { return $this->signedPdf; }

    /** Returns the total monetary amount of the contract. */
    public function totalAmount(): float { return $this->totalAmount; }

    /**
     * Uploads a signed PDF document to the contract.
     *
     * Bloqueo de seguridad: solo contratos pendientes de documento pueden recibir PDF.
     * Una vez cargado el documento, no se permite sobrescribirlo para garantizar la
     * integridad del expediente contractual.
     *
     * @param SignedPdf $pdf The signed PDF value object to attach
     * @throws \DomainException if the current status does not allow document upload
     */
    /** @throws \DomainException */
    public function uploadDocument(SignedPdf $pdf): void
    {
        if (!$this->status->canUploadDocument()) {
            throw new \DomainException(
                __('domain.contract.pending_document_status_required')
            );
        }
        $this->signedPdf = $pdf;
        $this->status = ContractStatus::DOCUMENT_LOADED;
    }

    /**
     * Activates the contract, making it legally effective.
     *
     * Bloqueo de seguridad: no se puede activar sin PDF firmado cargado en la plataforma.
     * Se verifica que el SignedPdf no sea nulo y que tenga una ruta registrada en el sistema.
     * La existencia física del archivo se valida al momento del upload. Si el path existe
     * en base de datos, el PDF fue cargado correctamente.
     *
     * @throws \DomainException if the contract cannot be activated
     */
    /** @throws \DomainException */
    public function activate(): void
    {
        if (!$this->status->canActivate()) {
            throw new \DomainException(
                __('domain.contract.cannot_activate_without_pdf')
            );
        }
        // Validación de seguridad: el PDF debe estar registrado en el sistema
        // (path guardado en BD). La existencia física del archivo se valida
        // al momento del upload. Si el path existe en BD, el PDF fue cargado.
        if ($this->signedPdf === null || empty($this->signedPdf->path())) {
            throw new \DomainException(
                __('domain.contract.cannot_activate_without_pdf')
            );
        }
        $this->status = ContractStatus::ACTIVE;
        $this->activatedAt = new \DateTimeImmutable();
        $this->startDate = new \DateTimeImmutable();
    }

    /**
     * Anulates (cancels) an active contract with a mandatory reason.
     *
     * Regla de negocio: solo contratos activos pueden anularse.
     * La anulación es un estado terminal e irreversible. Se requiere una razón
     * obligatoria para mantener la trazabilidad de la decisión.
     *
     * @param string $reason The reason why the contract is being cancelled
     * @throws \DomainException if the current status does not allow cancellation
     */
    /** @throws \DomainException */
    public function anulate(string $reason): void
    {
        if (!$this->status->canAnulate()) {
            throw new \DomainException(
                __('domain.contract.only_active_can_be_cancelled')
            );
        }
        $this->status = ContractStatus::CANCELLED;
        $this->cancelledAt = new \DateTimeImmutable();
        $this->cancellationReason = $reason;
    }
}
