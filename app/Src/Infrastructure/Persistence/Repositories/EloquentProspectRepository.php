<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Prospect as ProspectEntity;
use App\Src\Domain\Repositories\ProspectRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Prospect as ProspectModel;

class EloquentProspectRepository implements ProspectRepositoryInterface
{
    public function findById(int $id): ?ProspectEntity
    {
        $model = ProspectModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(ProspectEntity $prospect): void
    {
        ProspectModel::updateOrCreate(
            ['id' => $prospect->id()],
            [
                'company_name' => $prospect->companyName(),
                'contact_name' => $prospect->contactName(),
                'email' => $prospect->email(),
                'phone' => $prospect->phone(),
                'status' => $prospect->status(),
                'notes' => $prospect->notes(),
                'created_by' => $prospect->createdBy(),
            ]
        );
    }

    private function toEntity(ProspectModel $model): ProspectEntity
    {
        return new ProspectEntity(
            id: $model->id,
            companyName: $model->company_name,
            contactName: $model->contact_name,
            email: $model->email,
            phone: $model->phone,
            status: $model->status,
            notes: $model->notes,
            createdBy: $model->created_by,
        );
    }
}
