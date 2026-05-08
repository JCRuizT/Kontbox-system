<?php

namespace App\Src\Domain\Enums;

/**
 * Defines the possible states of a contract throughout its lifecycle.
 *
 * Contracts follow a strict state machine: PENDING_DOCUMENT -> DOCUMENT_LOADED -> ACTIVE -> CANCELLED.
 * Security validations are enforced before each transition.
 */
enum ContractStatus: string
{
    /** Regla de negocio: contrato recién creado, esperando la carga del PDF firmado. Bloqueo de seguridad: no se puede activar sin documento. */
    case PENDING_DOCUMENT = 'pending_document';

    /** Regla de negocio: PDF firmado cargado exitosamente. El contrato aún no está activo. Bloqueo de seguridad: el PDF debe existir físicamente en disco. */
    case DOCUMENT_LOADED = 'document_loaded';

    /** Regla de negocio: contrato activo con vigencia iniciada. Solo desde este estado se puede anular. */
    case ACTIVE = 'active';

    /** Regla de negocio: contrato anulado. Estado terminal. No se permiten más transiciones. */
    case CANCELLED = 'cancelled';

    /**
     * Returns the human-readable label for the current status.
     * Uses Laravel's __() helper for i18n support.
     */
    public function label(): string
    {
        return __("domain.contract_statuses.{$this->value}");
    }

    /**
     * Checks whether a PDF document can be uploaded for this contract.
     *
     * Regla de negocio: solo contratos en estado PENDING_DOCUMENT pueden recibir la carga de un PDF firmado.
     * Bloqueo de seguridad: una vez cargado el documento, no se permite sobrescribirlo para evitar
     * manipulación de documentos contractuales.
     */
    public function canUploadDocument(): bool
    {
        return $this === self::PENDING_DOCUMENT;
    }

    /**
     * Checks whether the contract can be activated.
     *
     * Regla de negocio: solo contratos con documento cargado (DOCUMENT_LOADED) pueden activarse.
     * Bloqueo de seguridad: se requiere que el PDF firmado esté registrado en el sistema antes de activar.
     */
    public function canActivate(): bool
    {
        return $this === self::DOCUMENT_LOADED;
    }

    /**
     * Checks whether the contract can be cancelled/anulled.
     *
     * Regla de negocio: solo contratos activos (ACTIVE) pueden anularse.
     * Un contrato en PENDING_DOCUMENT o DOCUMENT_LOADED debe primero activarse o gestionarse por separado.
     */
    public function canAnulate(): bool
    {
        return $this === self::ACTIVE;
    }
}
