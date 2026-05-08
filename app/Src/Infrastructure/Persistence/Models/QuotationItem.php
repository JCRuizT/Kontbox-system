<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa una partida (item) dentro de una cotización.
 * Almacena un snapshot del nombre y descripción del microservicio
 * al momento de la cotización, para preservar el histórico aunque
 * el catálogo cambie en el futuro.
 */
class QuotationItem extends Model
{
    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id', 'microservice_id', 'service_name_snapshot',
        'description_snapshot', 'quantity', 'unit_price', 'total_price',
    ];

    /**
     * unit_price/total_price: valores monetarios con 2 decimales.
     * Los snapshots preservan el nombre y descripción del servicio al cotizar.
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Cotización a la que pertenece esta partida.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Microservicio del catálogo en el momento de la cotización.
     */
    public function microservice(): BelongsTo
    {
        return $this->belongsTo(Microservice::class);
    }
}
