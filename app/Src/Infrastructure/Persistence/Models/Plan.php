<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name', 'description', 'is_active', 'is_custom', 'parent_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(PlanService::class);
    }

    public function parentPlan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_plan_id');
    }

    public function childPlans(): HasMany
    {
        return $this->hasMany(self::class, 'parent_plan_id');
    }

    /**
     * Actividades habilitadas/deshabilitadas dentro de este plan.
     */
    public function planActivities(): HasMany
    {
        return $this->hasMany(PlanActivity::class);
    }

    /**
     * Sincroniza las actividades del plan con las actividades de todos los microservicios
     * incluidos en los servicios del plan. Agrega nuevas y elimina huérfanas.
     */
    public function syncActivities(): void
    {
        $this->loadMissing('services.microservice.activities');

        $expectedActivityIds = $this->services
            ->flatMap(fn ($svc) => $svc->microservice?->activities?->pluck('id') ?? [])
            ->unique()
            ->values()
            ->toArray();

        $existingIds = $this->planActivities()->pluck('activity_id')->toArray();

        $toRemove = array_diff($existingIds, $expectedActivityIds);
        $toAdd = array_diff($expectedActivityIds, $existingIds);

        if (!empty($toRemove)) {
            $this->planActivities()->whereIn('activity_id', $toRemove)->delete();
        }

        foreach ($toAdd as $activityId) {
            $this->planActivities()->create([
                'activity_id' => $activityId,
                'is_enabled' => true,
            ]);
        }
    }
}
