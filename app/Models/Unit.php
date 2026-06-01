<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    protected $fillable = [
        'property_id',
        'unit_number',
        'type',
        'floor',
        'size_sqft',
        'status',
        'notes',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->whereIn('status', ['pending_deposit', 'active', 'terminating']);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function markOccupied(): void
    {
        $this->update(['status' => 'occupied']);
    }

    public function markAvailable(): void
    {
        $this->update(['status' => 'available']);
    }
}
