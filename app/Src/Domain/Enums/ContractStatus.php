<?php

namespace App\Src\Domain\Enums;

/**
 * Define los estados posibles de un contract a lo largo de su ciclo de vida.
 *
 * Los contratos siguen una máquina de estados estricta: PENDING_DOCUMENT → DOCUMENT_LOADED → ACTIVE → CANCELLED.
 * Validaciones de seguridad se aplican antes de cada transición.
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
     * Retorna la etiqueta legible para el estado actual.
     * Usa el helper __() de Laravel para soporte i18n.
     */
    public function label(): string
    {
        return __("domain.contract_statuses.{$this->value}");
    }

    /**
     * Verifica si se puede cargar un documento PDF para este contrato.
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
     * Verifica si el contrato puede ser activado.
     *
     * Regla de negocio: solo contratos con documento cargado (DOCUMENT_LOADED) pueden activarse.
     * Bloqueo de seguridad: se requiere que el PDF firmado esté registrado en el sistema antes de activar.
     */
    public function canActivate(): bool
    {
        return $this === self::DOCUMENT_LOADED;
    }

    /**
     * Verifica si el contrato puede ser anulado.
     *
     * Regla de negocio: solo contratos activos (ACTIVE) pueden anularse.
     * Un contrato en PENDING_DOCUMENT o DOCUMENT_LOADED debe primero activarse o gestionarse por separado.
     */
    public function canAnulate(): bool
    {
        return $this === self::ACTIVE;
    }
}
