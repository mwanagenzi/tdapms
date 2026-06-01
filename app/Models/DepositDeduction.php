<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositDeduction extends Model
{
    protected $fillable = [
        'lease_id',
        'inspection_report_item_id',
        'reason',
        'description',
        'amount',
        'status',
        'reviewed_by_id',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function inspectionReportItem(): BelongsTo
    {
        return $this->belongsTo(InspectionReportItem::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'  => ['label' => 'Pending Review', 'color' => 'amber'],
            'approved' => ['label' => 'Approved', 'color' => 'green'],
            'rejected' => ['label' => 'Rejected', 'color' => 'red'],
            default    => ['label' => ucfirst($this->status), 'color' => 'zinc'],
        };
    }
}
