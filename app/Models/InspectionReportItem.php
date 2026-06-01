<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionReportItem extends Model
{
    protected $fillable = [
        'inspection_report_id',
        'category',
        'item_name',
        'condition',
        'notes',
    ];

    public function inspectionReport(): BelongsTo
    {
        return $this->belongsTo(InspectionReport::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionReportPhoto::class);
    }

    public function getConditionBadgeAttribute(): array
    {
        return match ($this->condition) {
            'good'    => ['label' => 'Good', 'color' => 'green'],
            'fair'    => ['label' => 'Fair', 'color' => 'amber'],
            'damaged' => ['label' => 'Damaged', 'color' => 'red'],
            'missing' => ['label' => 'Missing', 'color' => 'zinc'],
            default   => ['label' => ucfirst($this->condition), 'color' => 'zinc'],
        };
    }
}
