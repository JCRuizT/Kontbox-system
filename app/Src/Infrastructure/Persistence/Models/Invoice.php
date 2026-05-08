<?php

namespace App\Src\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa una factura representativa (sin validez fiscal electrónica).
 * Es un documento informativo/comercial asociado a un contrato activo.
 * No reemplaza una factura fiscal (CFDI) - es solo representativa.
 */
class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number', 'contract_id', 'amount',
        'issued_date', 'status', 'notes', 'created_by',
    ];

    /**
     * amount: monto total con 2 decimales. issued_date: fecha de emisión.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_date' => 'date',
        ];
    }

    /**
     * Contrato al que pertenece esta factura.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Usuario que emitió la factura.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
