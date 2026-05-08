<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo pivote que asocia un microservicio a un plan con cantidad y precio personalizado.
 * Permite sobrescribir el precio base del microservicio para un plan específico.
 */
class PlanService extends Model
{
    protected $table = 'plan_services';

    protected $fillable = [
        'plan_id', 'microservice_id', 'custom_price',
    ];

    /**
     * custom_price: precio personalizado para este plan (opcional, si es null se usa base_cost).
     */
    protected function casts(): array
    {
        return [
            'custom_price' => 'decimal:2',
        ];
    }

    /**
     * Plan al que pertenece este servicio.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Microservicio del catálogo asociado.
     */
    public function microservice(): BelongsTo
    {
        return $this->belongsTo(Microservice::class);
    }
}
