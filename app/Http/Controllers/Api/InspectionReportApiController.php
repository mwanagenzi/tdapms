<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InspectionReportApiController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $reports = $tenant->leases()
            ->with(['inspectionReports.conductedBy', 'unit.property'])
            ->get()
            ->flatMap(fn ($lease) => $lease->inspectionReports->map(fn ($r) => [
                'id'            => $r->id,
                'lease_id'      => $r->lease_id,
                'unit_number'   => $lease->unit->unit_number,
                'property_name' => $lease->unit->property->name,
                'type'          => $r->type,
                'type_label'    => $r->type_label,
                'status'        => $r->status,
                'conducted_by'  => $r->conductedBy->name,
                'date'          => $r->created_at->toISOString(),
                'completed_at'  => $r->completed_at?->toISOString(),
                'notes'         => $r->notes,
            ]));

        return response()->json(['data' => $reports->values()]);
    }

    public function show(Request $request, $inspectionId)
    {
        $tenant = $request->user()->tenant;

        $leaseIds = $tenant->leases()->pluck('id');

        $inspection = \App\Models\InspectionReport::with(['conductedBy', 'items.photos', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->find($inspectionId);

        if (! $inspection) {
            return response()->json(['message' => 'Inspection report not found.'], 404);
        }

        return response()->json(['data' => [
            'id'            => $inspection->id,
            'type'          => $inspection->type,
            'type_label'    => $inspection->type_label,
            'status'        => $inspection->status,
            'unit_number'   => $inspection->lease->unit->unit_number,
            'property_name' => $inspection->lease->unit->property->name,
            'conducted_by'  => $inspection->conductedBy->name,
            'notes'         => $inspection->notes,
            'date'          => $inspection->created_at->toISOString(),
            'items'         => $inspection->items->map(fn ($item) => [
                'id'        => $item->id,
                'category'  => $item->category,
                'item_name' => $item->item_name,
                'condition' => $item->condition,
                'notes'     => $item->notes,
                'photos'    => $item->photos->map(fn ($p) => [
                    'url'     => $p->url,
                    'caption' => $p->caption,
                ]),
            ]),
        ]]);
    }
}
