<?php

/**
 * Configuración global de la aplicación Kontbox.
 *
 * Define constantes de negocio: tasa impositiva, paginación,
 * límites de archivos, moneda, moneda local e idiomas disponibles.
 */
return [
    'tax_rate' => env('KONTBOX_TAX_RATE', 0.19),
    'items_per_page' => env('KONTBOX_ITEMS_PER_PAGE', 15),
    'max_pdf_size_kb' => env('KONTBOX_MAX_PDF_SIZE', 10240),
    'quotation_valid_days' => env('KONTBOX_QUOTATION_VALID_DAYS', 15),
    'max_upload_size_kb' => 10240,
    'currency' => 'COP',
    'currency_symbol' => '$',
    'locale' => 'es',
    'fallback_locale' => 'en',
    'available_locales' => ['es', 'en'],
];
