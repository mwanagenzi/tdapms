<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class TenantProfileApiController extends Controller
{
    /**
     * GET /api/profile
     * Returns the authenticated tenant's full profile.
     */
    public function show(Request $request)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $activeLease = $tenant->activeLease;

        return response()->json([
            'data' => [
                // User account fields
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,

                // Tenant-specific fields
                'tenant' => [
                    'id'                      => $tenant->id,
                    'id_number'               => $tenant->id_number,
                    'emergency_contact_name'  => $tenant->emergency_contact_name,
                    'emergency_contact_phone' => $tenant->emergency_contact_phone,
                    'status'                  => $tenant->status,
                ],

                // Active / latest lease summary (nullable)
                'active_lease' => $activeLease ? [
                    'id'           => $activeLease->id,
                    'status'       => $activeLease->status,
                    'monthly_rent' => $activeLease->monthly_rent,
                    'start_date'   => $activeLease->start_date->toDateString(),
                    'end_date'     => $activeLease->end_date?->toDateString(),
                    'unit'         => [
                        'id'          => $activeLease->unit->id,
                        'unit_number' => $activeLease->unit->unit_number,
                        'property'    => [
                            'id'   => $activeLease->unit->property->id,
                            'name' => $activeLease->unit->property->name,
                        ],
                    ],
                ] : null,
            ],
        ]);
    }

    /**
     * PUT /api/profile
     * Update allowed profile fields.
     * Password change is optional — only processed when current_password is supplied.
     */
    public function update(Request $request)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $validated = $request->validate([
            'name'                    => ['sometimes', 'string', 'max:255'],
            'phone'                   => ['sometimes', 'string', 'max:20'],
            'id_number'               => ['sometimes', 'nullable', 'string', 'max:50'],
            'emergency_contact_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            // Password change fields — only validated when current_password is present
            'current_password'        => ['sometimes', 'required_with:new_password', 'string'],
            'new_password'            => ['sometimes', 'required_with:current_password', 'confirmed', Password::min(8)],
        ]);

        // Handle password change
        if (isset($validated['current_password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The current password is incorrect.'],
                ]);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        // Update user account fields
        if (isset($validated['name']))  $user->name  = $validated['name'];
        if (isset($validated['phone'])) $user->phone = $validated['phone'];
        $user->save();

        // Update tenant profile fields
        $tenantFields = array_filter([
            'id_number'               => $validated['id_number'] ?? null,
            'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
        ], fn ($v, $k) => array_key_exists($k, $validated), ARRAY_FILTER_USE_BOTH);

        if (! empty($tenantFields)) {
            $tenant->update($tenantFields);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tenant' => [
                    'id'                      => $tenant->id,
                    'id_number'               => $tenant->id_number,
                    'emergency_contact_name'  => $tenant->emergency_contact_name,
                    'emergency_contact_phone' => $tenant->emergency_contact_phone,
                    'status'                  => $tenant->status,
                ],
            ],
        ]);
    }
}
