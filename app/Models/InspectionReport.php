<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionReport extends Model
{
    protected $fillable = [
        'lease_id',
        'conducted_by_id',
        'type',
        'status',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionReportItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'move_in'  => 'Move-In',
            'move_out' => 'Move-Out',
            default    => ucfirst($this->type),
        };
    }
}
