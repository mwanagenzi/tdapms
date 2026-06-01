<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Property::with(['units', 'caretakers.user']);

        if ($user->hasRole('landlord')) {
            $query->where('landlord_id', $user->landlord->id);
        } elseif ($user->hasRole('caretaker')) {
            $caretakerPropertyIds = $user->caretaker->properties()->pluck('properties.id');
            $query->whereIn('id', $caretakerPropertyIds);
        }

        $properties = $query->latest()->paginate(15);

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'address'         => ['required', 'string'],
            'city'            => ['required', 'string', 'max:100'],
            'type'            => ['required', 'in:apartment,commercial,mixed'],
            'number_of_units' => ['required', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],
        ]);

        $validated['landlord_id'] = $request->user()->landlord->id;

        $property = Property::create($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with('success', "Property \"{$property->name}\" created successfully.");
    }

    public function show(Property $property)
    {
        $this->authorizePropertyAccess($property, request()->user());

        $property->load(['units.activeLease.tenant.user', 'caretakers.user']);

        return view('properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $this->authorizePropertyAccess($property, request()->user());

        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizePropertyAccess($property, $request->user());

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'address'         => ['required', 'string'],
            'city'            => ['required', 'string', 'max:100'],
            'type'            => ['required', 'in:apartment,commercial,mixed'],
            'number_of_units' => ['required', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],
        ]);

        $property->update($validated);

        return redirect()
            ->route('properties.show', $property)
            ->with('success', "Property updated successfully.");
    }

    public function destroy(Property $property)
    {
        $this->authorizePropertyAccess($property, request()->user());

        if ($property->units()->whereHas('activeLease')->exists()) {
            return back()->with('error', 'Cannot delete a property with active tenants.');
        }

        $property->delete();

        return redirect()
            ->route('properties.index')
            ->with('success', "Property deleted.");
    }

    private function authorizePropertyAccess(Property $property, $user): void
    {
        if ($user->hasRole('landlord') && $property->landlord_id !== $user->landlord->id) {
            abort(403);
        }

        if ($user->hasRole('caretaker')) {
            $assigned = $user->caretaker->properties()->where('properties.id', $property->id)->exists();
            if (! $assigned) {
                abort(403);
            }
        }
    }
}
