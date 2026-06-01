<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'user_id',
        'id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->whereIn('status', ['pending_deposit', 'active', 'terminating'])->latest();
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email ?? '';
    }

    public function getPhoneAttribute(): string
    {
        return $this->user->phone ?? '';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
