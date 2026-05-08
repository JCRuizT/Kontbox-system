<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo pivote que representa un servicio dentro de un contrato.
 * Almacena el microservicio, cantidad, precio unitario y precio total
 * al momento de la creación del contrato (snapshot de la cotización).
 */
class ContractService extends Model
{
    protected $table = 'contract_services';

    protected $fillable = [
        'contract_id', 'microservice_id', 'unit_price', 'total_price', 'is_enabled',
    ];

    /**
     * unit_price/total_price: valores monetarios con 2 decimales.
     * is_enabled: indica si el servicio está activo en el contrato.
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Contrato al que pertenece este servicio.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Microservicio del catálogo asociado a este servicio.
     */
    public function microservice(): BelongsTo
    {
        return $this->belongsTo(Microservice::class);
    }
}
