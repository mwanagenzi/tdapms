<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'unit_id',
        'tenant_id',
        'title',
        'description',
        'priority',
        'status',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MaintenanceUpdate::class);
    }

    public function conversations(): MorphMany
    {
        return $this->morphMany(Conversation::class, 'context');
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'low'    => ['label' => 'Low', 'color' => 'zinc'],
            'medium' => ['label' => 'Medium', 'color' => 'blue'],
            'high'   => ['label' => 'High', 'color' => 'orange'],
            'urgent' => ['label' => 'Urgent', 'color' => 'red'],
            default  => ['label' => ucfirst($this->priority), 'color' => 'zinc'],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'submitted'   => ['label' => 'Submitted', 'color' => 'amber'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
            'completed'   => ['label' => 'Completed', 'color' => 'green'],
            'rejected'    => ['label' => 'Rejected', 'color' => 'red'],
            default       => ['label' => ucfirst($this->status), 'color' => 'zinc'],
        };
    }
}
