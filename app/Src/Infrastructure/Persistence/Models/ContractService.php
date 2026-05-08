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
        'contract_id', 'microservice_id', 'quantity',
        'unit_price', 'total_price',
    ];

    /**
     * unit_price/total_price: valores monetarios con 2 decimales.
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
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
