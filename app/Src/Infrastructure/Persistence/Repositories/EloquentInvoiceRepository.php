<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Invoice as InvoiceEntity;
use App\Src\Domain\Repositories\InvoiceRepositoryInterface;
use App\Src\Infrastructure\Persistence\Models\Invoice as InvoiceModel;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?InvoiceEntity
    {
        $model = InvoiceModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(InvoiceEntity $invoice): void
    {
        InvoiceModel::updateOrCreate(
            ['id' => $invoice->id()],
            [
                'invoice_number' => $invoice->invoiceNumber(),
                'contract_id' => $invoice->contractId(),
                'amount' => $invoice->amount(),
                'issued_date' => $invoice->issuedDate()->format('Y-m-d'),
                'status' => $invoice->status(),
                'notes' => $invoice->notes(),
                'created_by' => $invoice->createdBy(),
            ]
        );
    }

    private function toEntity(InvoiceModel $model): InvoiceEntity
    {
        return new InvoiceEntity(
            id: $model->id,
            invoiceNumber: $model->invoice_number,
            contractId: $model->contract_id,
            amount: $model->amount,
            issuedDate: new \DateTimeImmutable($model->issued_date),
            status: $model->status,
            notes: $model->notes,
            createdBy: $model->created_by,
        );
    }
}
