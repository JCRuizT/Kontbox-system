<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo pivote que asocia una actividad a un plan con estado habilitado/deshabilitado.
 * Permite definir qué actividades del catálogo están incluidas en un plan,
 * respetando que las actividades esenciales (is_essential) no puedan deshabilitarse.
 */
class PlanActivity extends Model
{
    protected $table = 'plan_activities';

    protected $fillable = [
        'plan_id', 'activity_id', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
