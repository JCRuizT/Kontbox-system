<?php

namespace App\Src\Domain\Enums;

/**
 * Define los estados posibles de una factura a lo largo de su ciclo de vida.
 *
 * Máquina de tres estados simple: emitida → pagada o anulada.
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
