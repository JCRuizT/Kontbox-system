<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa la instancia de una actividad dentro de un contrato.
 * Vincula una actividad del catálogo a un contrato específico
 * y permite dar seguimiento a su estado de ejecución.
 */
class ActivityInstance extends Model
{
    protected $table = 'activity_instances';

    protected $fillable = [
        'contract_id', 'activity_id', 'status',
    ];

    /**
     * Contrato al que pertenece esta instancia de actividad.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Actividad del catálogo asociada a esta instancia.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
