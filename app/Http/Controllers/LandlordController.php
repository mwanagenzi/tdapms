<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LandlordController extends Controller
{
    public function index()
    {
        $landlords = Landlord::with(['user', 'properties'])
            ->withCount('properties')
            ->latest()
            ->paginate(15);

        return view('landlords.index', compact('landlords'));
    }

    public function create()
    {
        return view('landlords.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:20'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string'],
            'city'         => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'],
            'password'          => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('landlord');

        Landlord::create([
            'user_id'      => $user->id,
            'company_name' => $validated['company_name'] ?? null,
            'address'      => $validated['address'] ?? null,
            'city'         => $validated['city'] ?? null,
        ]);

        return redirect()
            ->route('landlords.index')
            ->with('success', "Landlord {$user->name} created successfully.");
    }

    public function show(Landlord $landlord)
    {
        $landlord->load(['user', 'properties.units']);

        return view('landlords.show', compact('landlord'));
    }

    public function edit(Landlord $landlord)
    {
        return view('landlords.edit', compact('landlord'));
    }

    public function update(Request $request, Landlord $landlord)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string'],
            'city'         => ['nullable', 'string', 'max:100'],
        ]);

        $landlord->user->update([
            'name'  => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $landlord->update([
            'company_name' => $validated['company_name'] ?? null,
            'address'      => $validated['address'] ?? null,
            'city'         => $validated['city'] ?? null,
        ]);

        return redirect()
            ->route('landlords.show', $landlord)
            ->with('success', 'Landlord updated.');
    }

    public function destroy(Landlord $landlord)
    {
        if ($landlord->properties()->exists()) {
            return back()->with('error', 'Cannot delete a landlord with existing properties.');
        }

        $landlord->user->delete();

        return redirect()
            ->route('landlords.index')
            ->with('success', 'Landlord removed.');
    }
}
