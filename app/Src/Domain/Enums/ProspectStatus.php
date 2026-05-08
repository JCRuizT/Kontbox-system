<?php

namespace App\Src\Domain\Enums;

/**
 * Defines the stages of a prospect through the sales pipeline.
 *
 * Represents the typical CRM funnel: lead acquisition, contact, negotiation, and outcome.
 */
enum ProspectStatus: string
{
    /** Regla de negocio: prospecto recién registrado, sin contacto inicial. */
    case NEW = 'new';

    /** Regla de negocio: se ha establecido contacto con el prospecto. */
    case CONTACTED = 'contacted';

    /** Regla de negocio: el prospecto está en proceso de negociación activa. */
    case NEGOTIATION = 'negotiation';

    /** Regla de negocio: negociación exitosa, prospecto convertido en cliente. Estado terminal. */
    case WON = 'won';

    /** Regla de negocio: negociación fallida, prospecto descartado. Estado terminal. */
    case LOST = 'lost';

    /**
     * Returns the human-readable label for the current prospect status.
     * Uses Laravel's __() helper for i18n support.
     */
    public function label(): string
    {
        return __("domain.prospect.statuses.{$this->value}");
    }
}
