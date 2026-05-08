<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\ContractAmendment as AmendmentEntity;
use App\Src\Domain\Repositories\AmendmentRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment as AmendmentModel;

class EloquentAmendmentRepository implements AmendmentRepositoryInterface
{
    public function findById(int $id): ?AmendmentEntity
    {
        $model = AmendmentModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(AmendmentEntity $amendment): void
    {
        AmendmentModel::updateOrCreate(
            ['id' => $amendment->id()],
            [
                'contract_id' => $amendment->contractId(),
                'amendment_number' => $amendment->amendmentNumber(),
                'description' => $amendment->description(),
                'signed_pdf_path' => $amendment->signedPdfPath(),
                'modified_services' => $amendment->modifiedServices() ? json_encode($amendment->modifiedServices()) : null,
                'created_by' => $amendment->createdBy(),
            ]
        );
    }

    private function toEntity(AmendmentModel $model): AmendmentEntity
    {
        return new AmendmentEntity(
            id: $model->id,
            contractId: $model->contract_id,
            amendmentNumber: $model->amendment_number,
            description: $model->description,
            signedPdfPath: $model->signed_pdf_path,
            modifiedServices: $model->modified_services ? json_decode($model->modified_services, true) : null,
            createdBy: $model->created_by,
        );
    }
}
