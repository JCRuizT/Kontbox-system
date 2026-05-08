<?php

namespace App\Src\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id', 'microservice_id', 'service_name_snapshot',
        'description_snapshot', 'unit_price', 'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'excluded_activities' => 'array',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function microservice(): BelongsTo
    {
        return $this->belongsTo(Microservice::class);
    }

    public function getIncludedActivitiesAttribute()
    {
        $activities = $this->microservice->activities ?? collect();
        $excluded = $this->excluded_activities ?? [];
        return $activities->filter(fn ($a) => !in_array($a->id, $excluded));
    }
}
