<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa una actividad del catálogo.
 * Una actividad pertenece a UN microservicio (relación N:1).
 * Cada microservicio puede tener múltiples actividades.
 */
class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'microservice_id', 'name', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Microservicio al que pertenece esta actividad.
     * Relación N:1 — varias actividades pueden pertenecer a un mismo microservicio.
     */
    public function microservice(): BelongsTo
    {
        return $this->belongsTo(Microservice::class);
    }
}
