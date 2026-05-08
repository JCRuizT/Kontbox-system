<?php

namespace App\Src\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa un contrato comercial.
 * Ciclo de vida: pending_document -> document_loaded -> active -> cancelled.
 * Bloqueo de seguridad: no se activa sin haber cargado el PDF firmado.
 * Almacena metadatos del PDF firmado (ruta, nombre original, tamaño, fecha).
 */
class Contract extends Model
{
    protected $table = 'contracts';

    protected $fillable = [
        'contract_number', 'quotation_id', 'approved_by',
        'status', 'start_date', 'end_date', 'total_amount',
        'signed_pdf_uploaded_at', 'signed_pdf_path',
        'signed_pdf_original_name', 'signed_pdf_size',
        'activated_at', 'cancelled_at', 'cancellation_reason',
    ];

    /**
     * total_amount: monto total del contrato con 2 decimales.
     * signed_pdf_uploaded_at/activated_at/cancelled_at: fechas de eventos clave.
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_pdf_uploaded_at' => 'datetime',
            'activated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Cotización que dio origen a este contrato.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Usuario que aprobó la creación del contrato (gerencia).
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Servicios incluidos en el contrato (replicados desde la cotización).
     */
    public function services(): HasMany
    {
        return $this->hasMany(ContractService::class);
    }

    /**
     * Anexos/modificaciones realizadas al contrato durante su vigencia.
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(ContractAmendment::class);
    }

    /**
     * Facturas emitidas contra este contrato.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Instancias de actividad asociadas a este contrato.
     * Cada activity_instance representa una actividad del catálogo
     * habilitada o deshabilitada para este contrato específico.
     */
    public function activityInstances(): HasMany
    {
        return $this->hasMany(ActivityInstance::class);
    }
}
