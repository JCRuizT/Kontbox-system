<?php

namespace App\Src\Application\Services;

/**
 * Caso de uso: QuotationPricingService.
 */
class QuotationPricingService
{
    /**
     * Calcula el subtotal, impuesto y total de una cotizaci\u00f3n basado en los items.
     */

    public function calculate(array $items): array
    {
        $subtotal = collect($items)->sum(fn ($i) => $i['unit_price']);
        $tax = $subtotal * config('kontbox.tax_rate');
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
