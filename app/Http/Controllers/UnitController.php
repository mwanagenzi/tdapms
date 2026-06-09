<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Unit::with(['property', 'activeLease.tenant.user']);

        if ($user->hasRole('landlord')) {
            $query->whereHas('property', fn ($q) => $q->where('landlord_id', $user->landlord?->id ?? 0));
        } elseif ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereIn('property_id', $propertyIds);
        }

        $propertyFilter = $request->get('property_id');
        if ($propertyFilter) {
            $query->where('property_id', $propertyFilter);
        }

        $units = $query->latest()->paginate(20);

        $properties = $this->getLandlordProperties($user);

        return view('units.index', compact('units', 'properties', 'propertyFilter'));
    }

    public function create(Request $request)
    {
        $properties = $this->getLandlordProperties($request->user());

        return view('units.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_number' => ['required', 'string', 'max:50'],
            'type'        => ['required', 'in:bedsitter,1br,2br,3br,studio,commercial,other'],
            'floor'       => ['nullable', 'string', 'max:20'],
            'size_sqft'   => ['nullable', 'numeric', 'min:1'],
            'notes'       => ['nullable', 'string'],
        ]);

        $this->authorizePropertyId($validated['property_id'], $request->user());

        $validated['status'] = 'available';

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', "Unit {$validated['unit_number']} created successfully.");
    }

    public function show(Unit $unit)
    {
        $this->authorizeUnitAccess($unit, request()->user());

        $unit->load([
            'property',
            'activeLease.tenant.user',
            'activeLease.deposit',
            'leases' => fn ($q) => $q->latest()->take(5),
            'maintenanceRequests' => fn ($q) => $q->latest()->take(5),
        ]);

        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $this->authorizeUnitAccess($unit, request()->user());

        $properties = $this->getLandlordProperties(request()->user());

        return view('units.edit', compact('unit', 'properties'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorizeUnitAccess($unit, $request->user());

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_number' => ['required', 'string', 'max:50'],
            'type'        => ['required', 'in:bedsitter,1br,2br,3br,studio,commercial,other'],
            'floor'       => ['nullable', 'string', 'max:20'],
            'size_sqft'   => ['nullable', 'numeric', 'min:1'],
            'status'      => ['required', 'in:available,occupied,maintenance'],
            'notes'       => ['nullable', 'string'],
        ]);

        $unit->update($validated);

        return redirect()
            ->route('units.show', $unit)
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $this->authorizeUnitAccess($unit, request()->user());

        if ($unit->activeLease()->exists()) {
            return back()->with('error', 'Cannot delete a unit with an active lease.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit deleted.');
    }

    private function getLandlordProperties($user)
    {
        if ($user->hasRole('landlord')) {
            return Property::where('landlord_id', $user->landlord?->id ?? 0)->orderBy('name')->get();
        }

        if ($user->hasRole('caretaker')) {
            return $user->caretaker?->properties()->orderBy('name')->get() ?? collect();
        }

        return Property::orderBy('name')->get();
    }

    private function authorizePropertyId(int $propertyId, $user): void
    {
        if ($user->hasRole('landlord')) {
            $exists = Property::where('id', $propertyId)->where('landlord_id', $user->landlord?->id ?? 0)->exists();
            if (! $exists) {
                abort(403);
            }
        }
    }

    private function authorizeUnitAccess(Unit $unit, $user): void
    {
        if ($user->hasRole('landlord')) {
            if ($unit->property->landlord_id !== $user->landlord?->id) {
                abort(403);
            }
        }

        if ($user->hasRole('caretaker')) {
            $assigned = $user->caretaker?->properties()->where('properties.id', $unit->property_id)->exists() ?? false;
            if (! $assigned) {
                abort(403);
            }
        }
    }
}
