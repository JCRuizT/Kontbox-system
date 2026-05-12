<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Activity as ActivityEntity;
use App\Src\Domain\Repositories\ActivityRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Activity as ActivityModel;

class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function findById(int $id): ?ActivityEntity
    {
        $model = ActivityModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(ActivityEntity $activity): void
    {
        ActivityModel::updateOrCreate(
            ['id' => $activity->id()],
            [
                'microservice_id' => $activity->microserviceId(),
                'name' => $activity->name(),
                'description' => $activity->description(),
                'is_active' => $activity->isActive(),
                'is_essential' => $activity->isEssential(),
            ]
        );
    }

    public function findByMicroservice(int $microserviceId): array
    {
        return ActivityModel::where('microservice_id', $microserviceId)
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(ActivityModel $model): ActivityEntity
    {
        return new ActivityEntity(
            id: $model->id,
            microserviceId: $model->microservice_id,
            name: $model->name,
            description: $model->description,
            isActive: $model->is_active,
            isEssential: $model->is_essential,
        );
    }
}
