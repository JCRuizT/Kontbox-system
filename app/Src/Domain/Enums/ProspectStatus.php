<?php

namespace App\Src\Domain\Enums;

/**
 * Define las etapas de un prospecto a través del pipeline de ventas.
 *
 * Representa el funnel CRM típico: adquisición, contacto, negociación y resultado.
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
     * Usa el helper __() de Laravel para soporte i18n.
     */
    public function label(): string
    {
        return __("domain.prospect.statuses.{$this->value}");
    }
}
