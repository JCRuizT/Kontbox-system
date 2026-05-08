<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Plan as PlanEntity;
use App\Src\Domain\Repositories\PlanRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Plan as PlanModel;

class EloquentPlanRepository implements PlanRepositoryInterface
{
    public function findById(int $id): ?PlanEntity
    {
        $model = PlanModel::with('services')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(PlanEntity $plan): void
    {
        PlanModel::updateOrCreate(
            ['id' => $plan->id()],
            [
                'name' => $plan->name(),
                'description' => $plan->description(),
                'is_active' => $plan->isActive(),
                'is_custom' => $plan->isCustom(),
                'parent_plan_id' => $plan->parentPlanId(),
            ]
        );
    }

    public function findAllActive(): array
    {
        return PlanModel::where('is_active', true)
            ->with('services')
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(PlanModel $model): PlanEntity
    {
        return new PlanEntity(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            isActive: $model->is_active,
            services: $model->services->map(fn ($s) => [
                'microservice_id' => $s->microservice_id,
                'custom_price' => $s->custom_price,
            ])->toArray(),
            isCustom: $model->is_custom ?? false,
            parentPlanId: $model->parent_plan_id,
        );
    }
}
