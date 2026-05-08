<?php

namespace App\Src\Infrastructure\Persistence\Repositories;

use App\Src\Domain\Entities\Quotation as QuotationEntity;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use App\Src\Domain\ValueObjects\Money;
use App\Src\Infrastructure\Persistence\Models\Quotation as QuotationModel;

/**
 * Repositorio Eloquent para cotizaciones.
 * Implementa el mapeo entre el modelo JSON (QuotationModel) y la entidad de dominio (QuotationEntity).
 * Gestiona la persistencia de estados y versiones de cotizaciones.
 */
class EloquentQuotationRepository implements QuotationRepositoryInterface
{
    /**
     * Busca una cotización por ID y la convierte a entidad de dominio.
     * Retorna null si no existe.
     */
    public function findById(int $id): ?QuotationEntity
    {
        $model = QuotationModel::with(['items', 'prospect'])->find($id);
        if (!$model) {
            return null;
        }
        return $this->toEntity($model);
    }

    /**
     * Persiste los cambios de la entidad de dominio Quotation en la base de datos.
     * Actualiza quote_number, status y version. Usa updateOrCreate
     * para insertar o actualizar según exista el registro.
     */
    public function save(QuotationEntity $quotation): void
    {
        $data = [
            'quote_number' => $quotation->quoteNumber(),
            'status' => $quotation->status()->value,
            'version' => $quotation->version(),
        ];

        QuotationModel::updateOrCreate(
            ['id' => $quotation->id()],
            $data
        );
    }

    /**
     * Busca la cotización más reciente de un prospecto (por versión descendente).
     * Útil para conocer el historial de cotizaciones de un cliente potencial.
     */
    public function findLatestByProspect(int $prospectId): ?QuotationEntity
    {
        $model = QuotationModel::where('prospect_id', $prospectId)
            ->orderByDesc('version')
            ->first();

        if (!$model) {
            return null;
        }
        return $this->toEntity($model);
    }

    /**
     * Mapea un modelo Eloquent (QuotationModel) a una entidad de dominio (QuotationEntity).
     * Convierte valores monetarios a ValueObject Money y el string de estado al enum QuotationStatus.
     * Incluye los items de la cotización como array.
     */
    private function toEntity(QuotationModel $model): QuotationEntity
    {
        return new QuotationEntity(
            id: $model->id,
            quoteNumber: $model->quote_number,
            prospectId: $model->prospect_id,
            planId: $model->plan_id,
            createdBy: $model->created_by,
            status: QuotationStatus::from($model->status),
            subtotal: new Money((float) $model->subtotal),
            tax: new Money((float) $model->tax),
            total: new Money((float) $model->total),
            validUntil: $model->valid_until,
            version: $model->version,
            parentId: $model->parent_id,
            rejectionReason: $model->rejection_reason,
            items: $model->items->toArray(),
        );
    }
}
