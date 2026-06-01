<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lease extends Model
{
    protected $fillable = [
        'tenant_id',
        'unit_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'deposit_amount',
        'status',
        'terminated_at',
        'terminated_by_id',
        'termination_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'terminated_at' => 'datetime',
            'monthly_rent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function terminatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terminated_by_id');
    }

    public function deposit(): HasOne
    {
        return $this->hasOne(Deposit::class);
    }

    public function inspectionReports(): HasMany
    {
        return $this->hasMany(InspectionReport::class);
    }

    public function depositDeductions(): HasMany
    {
        return $this->hasMany(DepositDeduction::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'context_id')
            ->where('context_type', Lease::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPendingDeposit(): bool
    {
        return $this->status === 'pending_deposit';
    }

    public function isTerminating(): bool
    {
        return $this->status === 'terminating';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending_deposit' => ['label' => 'Pending Deposit', 'color' => 'amber'],
            'active'          => ['label' => 'Active', 'color' => 'green'],
            'terminating'     => ['label' => 'Terminating', 'color' => 'orange'],
            'terminated'      => ['label' => 'Terminated', 'color' => 'zinc'],
            default           => ['label' => ucfirst($this->status), 'color' => 'zinc'],
        };
    }
}
