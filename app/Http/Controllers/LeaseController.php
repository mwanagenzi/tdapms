<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Lease::with(['tenant.user', 'unit.property', 'deposit']);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $statusFilter = $request->get('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $leases = $query->latest()->paginate(20);

        return view('leases.index', compact('leases', 'statusFilter'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $availableUnits = Unit::with('property')
            ->where('status', 'available')
            ->when($user->hasRole('caretaker'), function ($q) use ($user) {
                $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
                $q->whereIn('property_id', $propertyIds);
            })
            ->when($user->hasRole('landlord'), function ($q) use ($user) {
                $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
                $q->whereIn('property_id', $propertyIds);
            })
            ->get();

        $activeTenants = Tenant::with('user')
            ->where('status', 'active')
            ->whereDoesntHave('activeLease')
            ->get();

        $defaultDepositMonths = config('tdaps.deposit.default_deposit_months', 2);

        return view('leases.create', compact('availableUnits', 'activeTenants', 'defaultDepositMonths'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'      => ['required', 'exists:tenants,id'],
            'unit_id'        => ['required', 'exists:units,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after:start_date'],
            'monthly_rent'   => ['required', 'numeric', 'min:1'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string'],
        ]);

        $unit = Unit::findOrFail($validated['unit_id']);

        if (! $unit->isAvailable()) {
            return back()->with('error', 'The selected unit is not available.')->withInput();
        }

        $lease = Lease::create(array_merge($validated, ['status' => 'pending_deposit']));

        // Auto-create the deposit record
        Deposit::create([
            'lease_id'        => $lease->id,
            'amount_required' => $validated['deposit_amount'],
            'amount_paid'     => 0,
            'status'          => 'pending',
        ]);

        // Mark unit as occupied
        $unit->markOccupied();

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Lease created. Deposit collection can now be initiated.');
    }

    public function show(Lease $lease)
    {
        $lease->load([
            'tenant.user',
            'unit.property',
            'deposit.escrowTransactions',
            'inspectionReports',
            'depositDeductions',
            'conversations',
        ]);

        return view('leases.show', compact('lease'));
    }

    public function edit(Lease $lease)
    {
        abort_if(in_array($lease->status, ['terminated']), 403, 'Cannot edit a terminated lease.');

        $activeTenants = Tenant::with('user')->where('status', 'active')->get();
        $availableUnits = Unit::with('property')
            ->where(fn ($q) => $q->where('status', 'available')->orWhere('id', $lease->unit_id))
            ->get();

        return view('leases.edit', compact('lease', 'activeTenants', 'availableUnits'));
    }

    public function update(Request $request, Lease $lease)
    {
        abort_if($lease->status === 'terminated', 403);

        $validated = $request->validate([
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:1'],
            'notes'        => ['nullable', 'string'],
        ]);

        $lease->update($validated);

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Lease updated successfully.');
    }

    public function destroy(Lease $lease)
    {
        if (! in_array($lease->status, ['pending_deposit'])) {
            return back()->with('error', 'Only leases in pending_deposit status can be deleted. Use termination for active leases.');
        }

        $lease->unit->markAvailable();
        $lease->deposit?->delete();
        $lease->delete();

        return redirect()
            ->route('leases.index')
            ->with('success', 'Lease cancelled.');
    }
}
