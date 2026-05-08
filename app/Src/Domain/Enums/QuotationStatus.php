<?php

namespace App\Src\Domain\Enums;

/**
 * Defines the possible states of a quotation through its lifecycle.
 *
 * Each case represents a stage in the quotation's state machine.
 * Transitions are validated via permission methods below.
 */
enum QuotationStatus: string
{
    /** Regla de negocio: cotización recién creada, aún no enviada a revisión. Único estado que permite edición de datos. */
    case DRAFT = 'draft';

    /** Regla de negocio: cotización enviada a revisión. Ya no se puede modificar, solo aprobar o rechazar. */
    case UNDER_REVIEW = 'under_review';

    /** Regla de negocio: cotización aprobada. Lista para conversión a contrato. Estado terminal. */
    case APPROVED = 'approved';

    /** Regla de negocio: cotización rechazada por el revisor. Se requiere una razón de rechazo. Estado terminal. */
    case REJECTED = 'rejected';

    /**
     * Returns the human-readable label for the current status.
     * Uses Laravel's __() helper for i18n support.
     */
    public function label(): string
    {
        return __("domain.quotation.statuses.{$this->value}");
    }

    /**
     * Determines whether the quotation can be modified in its current status.
     *
     * Regla de negocio: solo las cotizaciones en estado borrador (DRAFT) pueden editarse.
     * Una vez enviadas a revisión, son inmutables para garantizar la trazabilidad.
     */
    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Checks if the quotation can be sent for approval workflow.
     *
     * Regla de negocio: solo se puede enviar a revisión desde el estado borrador.
     * Esto evita reenvíos accidentales de cotizaciones ya procesadas.
     */
    public function canBeSentForApproval(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Checks if the quotation can be approved.
     *
     * Regla de negocio: solo cotizaciones en revisión (UNDER_REVIEW) pueden ser aprobadas.
     * Una cotización en borrador primero debe enviarse a revisión.
     */
    public function canBeApproved(): bool
    {
        return $this === self::UNDER_REVIEW;
    }

    /**
     * Checks if the quotation can be rejected.
     *
     * Regla de negocio: solo cotizaciones en revisión (UNDER_REVIEW) pueden ser rechazadas.
     * El rechazo requiere una razón obligatoria proporcionada por el revisor.
     */
    public function canBeRejected(): bool
    {
        return $this === self::UNDER_REVIEW;
    }
}
