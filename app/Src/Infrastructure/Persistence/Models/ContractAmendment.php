<?php

namespace App\Src\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa un anexo o modificación a un contrato activo.
 * Cada anexo requiere un PDF firmado que respalde el cambio.
 * modified_services almacena en JSON los servicios modificados.
 */
class ContractAmendment extends Model
{
    protected $table = 'contract_amendments';

    protected $fillable = [
        'contract_id', 'amendment_number', 'description',
        'signed_pdf_path', 'modified_services', 'created_by',
    ];

    /**
     * modified_services: JSON con los servicios y precios modificados en este anexo.
     */
    protected function casts(): array
    {
        return [
            'modified_services' => 'json',
        ];
    }

    /**
     * Contrato al que pertenece este anexo.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Usuario que registró el anexo.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
