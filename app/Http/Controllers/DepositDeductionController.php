<?php

namespace App\Http\Controllers;

use App\Models\DepositDeduction;
use App\Models\InspectionReportItem;
use App\Models\Lease;
use Illuminate\Http\Request;

class DepositDeductionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DepositDeduction::with(['lease.tenant.user', 'lease.unit.property', 'reviewedBy']);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker->properties()->pluck('properties.id');
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord->properties()->pluck('id');
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $statusFilter = $request->get('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $deductions = $query->latest()->paginate(20);

        return view('deposit-deductions.index', compact('deductions', 'statusFilter'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $leases = Lease::with(['tenant.user', 'unit.property'])
            ->whereIn('status', ['active', 'terminating'])
            ->when($user->hasRole('caretaker'), function ($q) use ($user) {
                $propertyIds = $user->caretaker->properties()->pluck('properties.id');
                $q->whereHas('unit', fn ($q2) => $q2->whereIn('property_id', $propertyIds));
            })
            ->when($user->hasRole('landlord'), function ($q) use ($user) {
                $propertyIds = $user->landlord->properties()->pluck('id');
                $q->whereHas('unit', fn ($q2) => $q2->whereIn('property_id', $propertyIds));
            })
            ->get();

        return view('deposit-deductions.create', compact('leases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id'                   => ['required', 'exists:leases,id'],
            'inspection_report_item_id'  => ['nullable', 'exists:inspection_report_items,id'],
            'reason'                     => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'amount'                     => ['required', 'numeric', 'min:1'],
        ]);

        DepositDeduction::create($validated);

        return redirect()
            ->route('deposit-deductions.index')
            ->with('success', 'Deduction recorded and sent for landlord approval.');
    }

    public function show(DepositDeduction $depositDeduction)
    {
        $depositDeduction->load([
            'lease.tenant.user',
            'lease.unit.property',
            'lease.deposit',
            'inspectionReportItem.inspectionReport',
            'reviewedBy',
        ]);

        return view('deposit-deductions.show', compact('depositDeduction'));
    }

    public function edit(DepositDeduction $depositDeduction)
    {
        abort_if(! $depositDeduction->isPending(), 403, 'Only pending deductions can be edited.');

        $leases = Lease::with(['tenant.user', 'unit.property'])
            ->whereIn('status', ['active', 'terminating'])
            ->get();

        return view('deposit-deductions.edit', compact('depositDeduction', 'leases'));
    }

    public function update(Request $request, DepositDeduction $depositDeduction)
    {
        abort_if(! $depositDeduction->isPending(), 403);

        $validated = $request->validate([
            'reason'      => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount'      => ['required', 'numeric', 'min:1'],
        ]);

        $depositDeduction->update($validated);

        return redirect()
            ->route('deposit-deductions.show', $depositDeduction)
            ->with('success', 'Deduction updated.');
    }

    public function destroy(DepositDeduction $depositDeduction)
    {
        abort_if(! $depositDeduction->isPending(), 403, 'Only pending deductions can be deleted.');

        $depositDeduction->delete();

        return redirect()
            ->route('deposit-deductions.index')
            ->with('success', 'Deduction removed.');
    }

    /**
     * Landlord approves a deduction.
     */
    public function approve(Request $request, DepositDeduction $deduction)
    {
        $request->validate([
            'review_notes' => ['nullable', 'string'],
        ]);

        abort_if(! $deduction->isPending(), 403, 'Deduction is not in pending status.');

        $deduction->update([
            'status'        => 'approved',
            'reviewed_by_id' => $request->user()->id,
            'reviewed_at'   => now(),
            'review_notes'  => $request->review_notes,
        ]);

        return back()->with('success', 'Deduction approved. It will be applied to the net refund.');
    }

    /**
     * Landlord rejects a deduction.
     */
    public function reject(Request $request, DepositDeduction $deduction)
    {
        $request->validate([
            'review_notes' => ['required', 'string'],
        ]);

        abort_if(! $deduction->isPending(), 403, 'Deduction is not in pending status.');

        $deduction->update([
            'status'        => 'rejected',
            'reviewed_by_id' => $request->user()->id,
            'reviewed_at'   => now(),
            'review_notes'  => $request->review_notes,
        ]);

        return back()->with('success', 'Deduction rejected.');
    }
}
