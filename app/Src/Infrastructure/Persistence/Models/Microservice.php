<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa un microservicio del catálogo de servicios técnicos.
 * Cada microservicio tiene actividades asociadas y puede incluirse en planes.
 */
class Microservice extends Model
{
    protected $table = 'microservices';

    protected $fillable = [
        'name', 'description', 'base_cost', 'type', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Actividades asociadas a este microservicio.
     * Relación 1:N — un microservicio puede tener varias actividades.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Planes que incluyen este microservicio (a través de plan_services).
     */
    public function plans()
    {
        return $this->hasMany(PlanService::class);
    }
}
