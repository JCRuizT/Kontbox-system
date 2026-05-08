<?php

namespace App\Src\Domain\Enums;

/**
 * Defines the possible states of an invoice through its lifecycle.
 *
 * Simple three-state machine: issued -> paid or cancelled.
 */
enum InvoiceStatus: string
{
    /** Regla de negocio: factura emitida y enviada al cliente, pendiente de pago. */
    case ISSUED = 'issued';

    /** Regla de negocio: factura pagada por el cliente. Estado terminal. */
    case PAID = 'paid';

    /** Regla de negocio: factura anulada por error o acuerdo. Estado terminal. */
    case CANCELLED = 'cancelled';
}
