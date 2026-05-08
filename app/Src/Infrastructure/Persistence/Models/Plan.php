<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa un plan comercial.
 * Los planes agrupan microservicios con cantidades y precios personalizados
 * para ofrecer paquetes prediseñados a los prospectos.
 */
class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name', 'description', 'is_active',
    ];

    /**
     * is_active: indica si el plan está disponible para nuevas cotizaciones.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Servicios (microservicios con cantidad y precio) que componen este plan.
     * Relación 1:N - un plan tiene varios servicios.
     */
    public function services(): HasMany
    {
        return $this->hasMany(PlanService::class);
    }
}
