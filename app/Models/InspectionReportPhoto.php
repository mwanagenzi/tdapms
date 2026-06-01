<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InspectionReportPhoto extends Model
{
    protected $fillable = [
        'inspection_report_item_id',
        'path',
        'caption',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InspectionReportItem::class, 'inspection_report_item_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
