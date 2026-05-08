<?php

namespace App\Src\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa una cotización.
 * Ciclo de vida: draft -> under_review -> approved | rejected.
 * Inmutabilidad: una vez enviada a aprobación no puede modificarse.
 * Soporta versionado: una cotización rechazada puede generar una nueva versión.
 * parent_id vincula versiones hijas con su cotización original.
 */
class Quotation extends Model
{
    protected $table = 'quotations';

    protected $fillable = [
        'quote_number', 'prospect_id', 'plan_id', 'created_by',
        'status', 'subtotal', 'tax', 'total', 'valid_until',
        'version', 'parent_id', 'rejection_reason',
    ];

    /**
     * Campos monetarios con 2 decimales (subtotal, tax, total).
     * valid_until: fecha hasta la cual la cotización tiene validez.
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    /**
     * Prospecto al que va dirigida esta cotización.
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * Plan seleccionado como base para esta cotización (opcional).
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario que creó la cotización.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Partidas (items) que componen la cotización con snapshot del servicio.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    /**
     * Cotización padre (versión anterior) si esta es una nueva versión.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Versiones hijas generadas a partir de esta cotización.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
