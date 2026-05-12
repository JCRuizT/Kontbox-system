<?php

namespace App\Src\Domain\Entities;

use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\ValueObjects\SignedPdf;

/**
 * Entidad que representa un contrato legal derivado de una cotización aprobada.
 *
 * Encapsula la raíz del agregado de contrato con ciclo de vida estricto de documento y activación.
 * Un contrato requiere un PDF firmado cargado antes de poder activarse.
 * Bloqueos de seguridad impiden la activación sin un documento almacenado físicamente.
 */
class Contract
{
    /**
     * @param int|null                 $id                 Identificador interno, null para nuevas entidades
     * @param string                   $contractNumber     Identificador único legible del contrato
     * @param int                      $quotationId        Cotización de origen que generó este contrato
     * @param int                      $approvedBy         ID del usuario que aprobó el contrato
     * @param ContractStatus           $status             Estado actual de la máquina de estados
     * @param SignedPdf|null           $signedPdf          Documento PDF firmado cargado en la plataforma
     * @param \DateTimeInterface|null  $startDate          Fecha de inicio de vigencia del contrato
     * @param \DateTimeInterface|null  $endDate            Fecha de vencimiento del contrato
     * @param float                    $totalAmount        Valor monetario del contrato
     * @param \DateTimeInterface|null  $activatedAt        Marca de tiempo cuando se activó el contrato
     * @param \DateTimeInterface|null  $cancelledAt        Marca de tiempo cuando se anuló el contrato
     * @param string|null              $cancellationReason Motivo de anulación
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

    /** Retorna el identificador interno de la base de datos. Null si aún no se ha persistido. */
    public function id(): ?int
{
    return $this->id;
}

    /** Retorna el número de contrato único legible. */
    public function contractNumber(): string
{
    return $this->contractNumber;
}

    /** Retorna el ID de la cotización de origen que generó este contrato. */
    public function quotationId(): int
{
    return $this->quotationId;
}

    /** Retorna el ID del usuario que aprobó el contrato. */
    public function approvedBy(): int
{
    return $this->approvedBy;
}

    /** Retorna el estado actual de la máquina de estados del contrato. */
    public function status(): ContractStatus
{
    return $this->status;
}

    /** Retorna el documento PDF firmado, o null si aún no se ha cargado. */
    public function signedPdf(): ?SignedPdf
{
    return $this->signedPdf;
}

    /** Retorna el monto monetario total del contrato. */
    public function totalAmount(): float
{
    return $this->totalAmount;
}

    /**
     * Carga un documento PDF firmado al contrato.
     *
     * Bloqueo de seguridad: solo contratos pendientes de documento pueden recibir PDF.
     * Una vez cargado el documento, no se permite sobrescribirlo para garantizar la
     * integridad del expediente contractual.
     *
     * @param SignedPdf $pdf El value object del PDF firmado a adjuntar
     * @throws \DomainException si el estado actual no permite la carga del documento
     */
    /** @throws \DomainException */
    public function uploadDocument(SignedPdf $pdf): void
    {
        if (!$this->status->canUploadDocument()) {
            throw new \DomainException(
                'domain.contract.pending_document_status_required'
            );
        }
        $this->signedPdf = $pdf;
        $this->status = ContractStatus::DOCUMENT_LOADED;
    }

    /**
     * Activa el contrato, haciéndolo legalmente efectivo.
     *
     * Bloqueo de seguridad: no se puede activar sin PDF firmado cargado en la plataforma.
     * Se verifica que el SignedPdf no sea nulo y que tenga una ruta registrada en el sistema.
     * La existencia física del archivo se valida al momento del upload. Si el path existe
     * en base de datos, el PDF fue cargado correctamente.
     *
     * @throws \DomainException si el contrato no puede ser activado
     */
    /** @throws \DomainException */
    public function activate(): void
    {
        if (!$this->status->canActivate()) {
            throw new \DomainException(
                'domain.contract.cannot_activate_without_pdf'
            );
        }
        if ($this->signedPdf === null || empty($this->signedPdf->path())) {
            throw new \DomainException(
                'domain.contract.cannot_activate_without_pdf'
            );
        }
        $this->status = ContractStatus::ACTIVE;
        $this->activatedAt = new \DateTimeImmutable();
        $this->startDate = new \DateTimeImmutable();
    }

    /**
     * Anula un contrato activo con un motivo obligatorio.
     *
     * Regla de negocio: solo contratos activos pueden anularse.
     * La anulación es un estado terminal e irreversible. Se requiere una razón
     * obligatoria para mantener la trazabilidad de la decisión.
     *
     * @param string $reason La razón por la cual se está cancelando contract
     * @throws \DomainException si el estado actual no permite la anulación
     */
    /** @throws \DomainException */
    public function anulate(string $reason): void
    {
        if (!$this->status->canAnulate()) {
            throw new \DomainException(
                'domain.contract.only_active_can_be_cancelled'
            );
        }
        $this->status = ContractStatus::CANCELLED;
        $this->cancelledAt = new \DateTimeImmutable();
        $this->cancellationReason = $reason;
    }
}
