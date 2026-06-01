<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $requests = MaintenanceRequest::with(['unit.property', 'updates' => fn ($q) => $q->latest()->take(1)])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $requests->map(fn ($r) => $this->formatRequest($r)),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $lease = $tenant->activeLease;

        if (! $lease) {
            return response()->json(['message' => 'No active lease found.'], 422);
        }

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
        ]);

        $mr = MaintenanceRequest::create(array_merge($validated, [
            'unit_id'   => $lease->unit_id,
            'tenant_id' => $tenant->id,
            'status'    => 'submitted',
        ]));

        return response()->json(['data' => $this->formatRequest($mr)], 201);
    }

    public function show(Request $request, $id)
    {
        $tenant = $request->user()->tenant;

        $mr = MaintenanceRequest::with(['unit.property', 'updates.updatedBy'])
            ->where('tenant_id', $tenant->id)
            ->find($id);

        if (! $mr) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        return response()->json(['data' => $this->formatRequest($mr, true)]);
    }

    private function formatRequest(MaintenanceRequest $r, bool $detailed = false): array
    {
        $base = [
            'id'            => $r->id,
            'title'         => $r->title,
            'description'   => $r->description,
            'priority'      => $r->priority,
            'priority_label' => $r->priority_badge['label'],
            'status'        => $r->status,
            'status_label'  => $r->status_badge['label'],
            'unit_number'   => $r->unit->unit_number,
            'property_name' => $r->unit->property->name,
            'created_at'    => $r->created_at->toISOString(),
        ];

        if ($detailed && $r->relationLoaded('updates')) {
            $base['updates'] = $r->updates->map(fn ($u) => [
                'status'     => $u->status,
                'notes'      => $u->notes,
                'updated_by' => $u->updatedBy->name,
                'date'       => $u->created_at->toISOString(),
            ]);
        }

        return $base;
    }
}
