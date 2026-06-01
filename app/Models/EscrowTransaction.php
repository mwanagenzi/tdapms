<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowTransaction extends Model
{
    protected $fillable = [
        'deposit_id',
        'type',
        'amount',
        'status',
        'mpesa_reference',
        'mpesa_checkout_request_id',
        'phone',
        'metadata',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'metadata'     => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCollection(): bool
    {
        return $this->type === 'collection';
    }

    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }
}
