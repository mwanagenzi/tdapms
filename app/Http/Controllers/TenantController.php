<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Tenant::with(['user', 'activeLease.unit.property']);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereHas('activeLease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('activeLease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $tenants = $query->latest()->paginate(20);

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'unique:users,email'],
            'phone'                   => ['required', 'string', 'max:20'],
            'password'                => ['required', 'string', 'min:8', 'confirmed'],
            'id_number'               => ['nullable', 'string', 'max:30'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'],
            'password'          => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'                 => $user->id,
            'id_number'               => $validated['id_number'] ?? null,
            'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'status'                  => 'active',
        ]);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', "Tenant {$user->name} created successfully.");
    }

    public function show(Tenant $tenant)
    {
        $tenant->load([
            'user',
            'leases.unit.property',
            'leases.deposit',
            'activeLease',
        ]);

        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'phone'                   => ['required', 'string', 'max:20'],
            'id_number'               => ['nullable', 'string', 'max:30'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $tenant->user->update([
            'name'  => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $tenant->update([
            'id_number'               => $validated['id_number'] ?? null,
            'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
        ]);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->activeLease()->exists()) {
            return back()->with('error', 'Cannot delete a tenant with an active lease. Terminate the lease first.');
        }

        $tenant->user->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant removed.');
    }
}
