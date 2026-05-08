<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Contract as ContractEntity;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Repositories\ContractRepositoryInterface;
use App\Src\Domain\ValueObjects\SignedPdf;
use App\Src\Infrastructure\Persistence\Models\Contract as ContractModel;

/**
 * Repositorio Eloquent para contratos.
 * Implementa el mapeo entre el modelo JSON (ContractModel) y la entidad de dominio (ContractEntity).
 * Centraliza la lógica de persistencia y reconstrucción de la entidad Contract.
 */
class EloquentContractRepository implements ContractRepositoryInterface
{
    /**
     * Busca un contrato por ID y lo convierte a entidad de dominio.
     * Retorna null si no existe.
     */
    public function findById(int $id): ?ContractEntity
    {
        $model = ContractModel::with(['services', 'quotation'])->find($id);
        if (!$model) {
            return null;
        }
        return $this->toEntity($model);
    }

    /**
     * Persiste los cambios de la entidad de dominio Contract en la base de datos.
     * Según el estado del contrato, actualiza: metadatos del PDF (document_loaded),
     * fechas de activación (active) o cancelación (cancelled).
     * Usa updateOrCreate para insertar o actualizar según exista el registro.
     */
    public function save(ContractEntity $contract): void
    {
        $data = [
            'status' => $contract->status()->value,
        ];

        if ($contract->status() === ContractStatus::DOCUMENT_LOADED && $contract->signedPdf()) {
            $pdf = $contract->signedPdf();
            $data['signed_pdf_path'] = $pdf->path();
            $data['signed_pdf_original_name'] = $pdf->originalName();
            $data['signed_pdf_size'] = $pdf->sizeInBytes();
            $data['signed_pdf_uploaded_at'] = now();
        }

        if ($contract->status() === ContractStatus::ACTIVE) {
            $data['start_date'] = now();
            $data['activated_at'] = now();
        }

        if ($contract->status() === ContractStatus::CANCELLED) {
            $data['cancelled_at'] = now();
        }

        ContractModel::updateOrCreate(
            ['id' => $contract->id()],
            $data
        );
    }

    /**
     * Busca todos los contratos por estado y los convierte a entidades de dominio.
     */
    public function findByStatus(string $status): array
    {
        return ContractModel::where('status', $status)
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->toArray();
    }

    /**
     * Mapea un modelo Eloquent (ContractModel) a una entidad de dominio (ContractEntity).
     * Construye el ValueObject SignedPdf si el contrato tiene PDF cargado.
     * Convierte el string de estado al enum ContractStatus.
     */
    private function toEntity(ContractModel $model): ContractEntity
    {
        $pdf = null;
        if ($model->signed_pdf_path) {
            $pdf = new SignedPdf(
                path: $model->signed_pdf_path,
                originalName: $model->signed_pdf_original_name ?? 'documento_firmado.pdf',
                sizeInBytes: $model->signed_pdf_size ?? 0,
            );
        }

        return new ContractEntity(
            id: $model->id,
            contractNumber: $model->contract_number,
            quotationId: $model->quotation_id,
            approvedBy: $model->approved_by,
            status: ContractStatus::from($model->status),
            signedPdf: $pdf,
            startDate: $model->start_date,
            endDate: $model->end_date,
            totalAmount: $model->total_amount,
            activatedAt: $model->activated_at,
            cancelledAt: $model->cancelled_at,
            cancellationReason: $model->cancellation_reason,
        );
    }
}
