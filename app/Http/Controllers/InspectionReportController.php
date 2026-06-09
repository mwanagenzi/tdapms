<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\InspectionReportPhoto;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InspectionReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = InspectionReport::with(['lease.tenant.user', 'lease.unit.property', 'conductedBy']);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $typeFilter = $request->get('type');
        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $reports = $query->latest()->paginate(20);

        return view('inspections.index', compact('reports', 'typeFilter'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $leases = Lease::with(['tenant.user', 'unit.property'])
            ->whereIn('status', ['pending_deposit', 'active', 'terminating'])
            ->when($user->hasRole('caretaker'), function ($q) use ($user) {
                $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
                $q->whereHas('unit', fn ($q2) => $q2->whereIn('property_id', $propertyIds));
            })
            ->when($user->hasRole('landlord'), function ($q) use ($user) {
                $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
                $q->whereHas('unit', fn ($q2) => $q2->whereIn('property_id', $propertyIds));
            })
            ->get();

        $preselectedLeaseId = $request->get('lease_id');

        return view('inspections.create', compact('leases', 'preselectedLeaseId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'type'     => ['required', 'in:move_in,move_out'],
            'notes'    => ['nullable', 'string'],
            'items'    => ['required', 'array', 'min:1'],
            'items.*.category'  => ['required', 'string'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.condition' => ['required', 'in:good,fair,damaged,missing'],
            'items.*.notes'     => ['nullable', 'string'],
            'photos'   => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $report = InspectionReport::create([
            'lease_id'        => $validated['lease_id'],
            'conducted_by_id' => $request->user()->id,
            'type'            => $validated['type'],
            'status'          => 'draft',
            'notes'           => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $itemData) {
            $item = InspectionReportItem::create([
                'inspection_report_id' => $report->id,
                'category'             => $itemData['category'],
                'item_name'            => $itemData['item_name'],
                'condition'            => $itemData['condition'],
                'notes'                => $itemData['notes'] ?? null,
            ]);

            // Handle photo uploads for this item
            $photoKey = "photos.{$index}";
            if ($request->hasFile($photoKey)) {
                foreach ($request->file($photoKey) as $photo) {
                    $path = $photo->store("inspections/{$report->id}", 'public');
                    InspectionReportPhoto::create([
                        'inspection_report_item_id' => $item->id,
                        'path'                      => $path,
                    ]);
                }
            }
        }

        return redirect()
            ->route('inspections.show', $report)
            ->with('success', ucfirst(str_replace('_', '-', $report->type)) . ' inspection report created.');
    }

    public function show(InspectionReport $inspection)
    {
        $inspection->load([
            'lease.tenant.user',
            'lease.unit.property',
            'conductedBy',
            'items.photos',
        ]);

        return view('inspections.show', compact('inspection'));
    }

    public function edit(InspectionReport $inspection)
    {
        abort_if($inspection->isCompleted(), 403, 'Completed inspection reports cannot be edited.');

        $inspection->load(['items.photos']);

        return view('inspections.edit', compact('inspection'));
    }

    public function update(Request $request, InspectionReport $inspection)
    {
        abort_if($inspection->isCompleted(), 403);

        $validated = $request->validate([
            'notes'    => ['nullable', 'string'],
            'items'    => ['required', 'array', 'min:1'],
            'items.*.id'        => ['nullable', 'exists:inspection_report_items,id'],
            'items.*.category'  => ['required', 'string'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.condition' => ['required', 'in:good,fair,damaged,missing'],
            'items.*.notes'     => ['nullable', 'string'],
        ]);

        $inspection->update(['notes' => $validated['notes'] ?? null]);

        // Delete removed items (items not in request)
        $incomingIds = collect($validated['items'])->pluck('id')->filter()->all();
        $inspection->items()->whereNotIn('id', $incomingIds)->delete();

        foreach ($validated['items'] as $itemData) {
            if (! empty($itemData['id'])) {
                InspectionReportItem::find($itemData['id'])?->update([
                    'category'  => $itemData['category'],
                    'item_name' => $itemData['item_name'],
                    'condition' => $itemData['condition'],
                    'notes'     => $itemData['notes'] ?? null,
                ]);
            } else {
                InspectionReportItem::create([
                    'inspection_report_id' => $inspection->id,
                    'category'             => $itemData['category'],
                    'item_name'            => $itemData['item_name'],
                    'condition'            => $itemData['condition'],
                    'notes'                => $itemData['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('inspections.show', $inspection)
            ->with('success', 'Inspection report updated.');
    }

    public function destroy(InspectionReport $inspection)
    {
        abort_if($inspection->isCompleted(), 403, 'Completed reports cannot be deleted.');

        $inspection->delete();

        return redirect()
            ->route('inspections.index')
            ->with('success', 'Inspection report deleted.');
    }

    /**
     * Mark an inspection report as completed.
     * Once completed, it cannot be edited or deleted.
     */
    public function complete(InspectionReport $inspection)
    {
        if ($inspection->isCompleted()) {
            return back()->with('error', 'Report is already completed.');
        }

        if ($inspection->items()->count() === 0) {
            return back()->with('error', 'Cannot complete a report with no items.');
        }

        $inspection->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Inspection report marked as completed.');
    }
}
