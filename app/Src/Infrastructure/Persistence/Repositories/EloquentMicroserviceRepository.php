<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Microservice as MicroserviceEntity;
use App\Src\Domain\Repositories\MicroserviceRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Microservice as MicroserviceModel;
use Illuminate\Support\Collection;

class EloquentMicroserviceRepository implements MicroserviceRepositoryInterface
{
    public function findById(int $id): ?MicroserviceEntity
    {
        $model = MicroserviceModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(MicroserviceEntity $microservice): void
    {
        MicroserviceModel::updateOrCreate(
            ['id' => $microservice->id()],
            [
                'name' => $microservice->name(),
                'description' => $microservice->description(),
                'base_cost' => $microservice->baseCost(),
                'type' => $microservice->type(),
                'is_active' => $microservice->isActive(),
            ]
        );
    }

    public function findAllActive(): array
    {
        return MicroserviceModel::where('is_active', true)
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(MicroserviceModel $model): MicroserviceEntity
    {
        return new MicroserviceEntity(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            baseCost: $model->base_cost,
            type: $model->type,
            isActive: $model->is_active,
        );
    }
}
