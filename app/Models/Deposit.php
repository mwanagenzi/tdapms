<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $fillable = [
        'lease_id',
        'amount_required',
        'amount_paid',
        'status',
        'refund_initiated_at',
        'refund_initiated_by_id',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_required'     => 'decimal:2',
            'amount_paid'         => 'decimal:2',
            'refund_initiated_at' => 'datetime',
            'refunded_at'         => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function refundInitiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refund_initiated_by_id');
    }

    public function escrowTransactions(): HasMany
    {
        return $this->hasMany(EscrowTransaction::class);
    }

    public function getNetRefundAmountAttribute(): float
    {
        $approvedDeductions = $this->lease->depositDeductions()
            ->where('status', 'approved')
            ->sum('amount');

        return max(0, (float) $this->amount_paid - $approvedDeductions);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->amount_paid >= $this->amount_required;
    }

    public function getOutstandingAttribute(): float
    {
        return max(0, (float) $this->amount_required - (float) $this->amount_paid);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'         => ['label' => 'Pending', 'color' => 'amber'],
            'partially_paid'  => ['label' => 'Partially Paid', 'color' => 'orange'],
            'held'            => ['label' => 'Held in Escrow', 'color' => 'green'],
            'refunding'       => ['label' => 'Refunding', 'color' => 'sky'],
            'refunded'        => ['label' => 'Refunded', 'color' => 'zinc'],
            default           => ['label' => ucfirst($this->status), 'color' => 'zinc'],
        };
    }
}
