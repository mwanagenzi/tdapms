<?php

namespace App\Http\Controllers;

use App\Models\Caretaker;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CaretakerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Caretaker::with(['user', 'properties']);

        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('properties', fn ($q) => $q->whereIn('properties.id', $propertyIds));
        }

        $caretakers = $query->latest()->paginate(15);

        return view('caretakers.index', compact('caretakers'));
    }

    public function create(Request $request)
    {
        $properties = $this->getLandlordProperties($request->user());

        return view('caretakers.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:20'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'id_number'    => ['nullable', 'string', 'max:30'],
            'property_ids' => ['nullable', 'array'],
            'property_ids.*' => ['exists:properties,id'],
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'password'           => Hash::make($validated['password']),
            'email_verified_at'  => now(),
        ]);

        $user->assignRole('caretaker');

        $caretaker = Caretaker::create([
            'user_id'   => $user->id,
            'id_number' => $validated['id_number'] ?? null,
        ]);

        if (! empty($validated['property_ids'])) {
            $this->authorizePropertyIds($validated['property_ids'], $request->user());
            $caretaker->properties()->sync($validated['property_ids']);
        }

        return redirect()
            ->route('caretakers.show', $caretaker)
            ->with('success', "Caretaker {$user->name} created successfully.");
    }

    public function show(Caretaker $caretaker)
    {
        $this->authorizeCaretakerAccess($caretaker, request()->user());

        $caretaker->load(['user', 'properties.units']);

        return view('caretakers.show', compact('caretaker'));
    }

    public function edit(Caretaker $caretaker)
    {
        $this->authorizeCaretakerAccess($caretaker, request()->user());

        $properties = $this->getLandlordProperties(request()->user());

        return view('caretakers.edit', compact('caretaker', 'properties'));
    }

    public function update(Request $request, Caretaker $caretaker)
    {
        $this->authorizeCaretakerAccess($caretaker, $request->user());

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20'],
            'id_number'      => ['nullable', 'string', 'max:30'],
            'property_ids'   => ['nullable', 'array'],
            'property_ids.*' => ['exists:properties,id'],
        ]);

        $caretaker->user->update([
            'name'  => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $caretaker->update(['id_number' => $validated['id_number'] ?? null]);

        $propertyIds = $validated['property_ids'] ?? [];
        if ($propertyIds) {
            $this->authorizePropertyIds($propertyIds, $request->user());
        }
        $caretaker->properties()->sync($propertyIds);

        return redirect()
            ->route('caretakers.show', $caretaker)
            ->with('success', 'Caretaker updated successfully.');
    }

    public function destroy(Caretaker $caretaker)
    {
        $this->authorizeCaretakerAccess($caretaker, request()->user());

        $caretaker->user->delete();

        return redirect()
            ->route('caretakers.index')
            ->with('success', 'Caretaker removed.');
    }

    private function getLandlordProperties($user)
    {
        if ($user->hasRole('landlord')) {
            return Property::where('landlord_id', $user->landlord?->id ?? 0)->orderBy('name')->get();
        }

        return Property::orderBy('name')->get();
    }

    private function authorizeCaretakerAccess(Caretaker $caretaker, $user): void
    {
        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $assigned = $caretaker->properties()->whereIn('properties.id', $propertyIds)->exists();
            if (! $assigned) {
                abort(403);
            }
        }
    }

    private function authorizePropertyIds(array $propertyIds, $user): void
    {
        if ($user->hasRole('landlord')) {
            $ownedIds = $user->landlord?->properties()->pluck('id') ?? collect()->toArray();
            foreach ($propertyIds as $id) {
                if (! in_array($id, $ownedIds)) {
                    abort(403);
                }
            }
        }
    }
}
