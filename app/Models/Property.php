<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'landlord_id',
        'name',
        'address',
        'city',
        'type',
        'number_of_units',
        'description',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function caretakers(): BelongsToMany
    {
        return $this->belongsToMany(Caretaker::class, 'property_caretaker');
    }

    public function getAvailableUnitsCountAttribute(): int
    {
        return $this->units()->where('status', 'available')->count();
    }

    public function getOccupiedUnitsCountAttribute(): int
    {
        return $this->units()->where('status', 'occupied')->count();
    }
}
