<?php

namespace App\Src\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa un prospecto (cliente potencial).
 * Almacena datos de contacto de la empresa y el estado en el pipeline de ventas
 * (new, contacted, negotiation, won, lost).
 */
class Prospect extends Model
{
    protected $table = 'prospects';

    protected $fillable = [
        'company_name', 'contact_name', 'email', 'phone',
        'status', 'notes', 'created_by',
    ];

    /**
     * Usuario que registró el prospecto en el sistema.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
