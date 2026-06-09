<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Lease::with(['unit.property', 'tenant.user', 'deposit']);

        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $activeLeases      = (clone $query)->where('status', 'active')->count();
        $terminatingLeases = (clone $query)->where('status', 'terminating')->count();
        $totalDepositsHeld = Deposit::whereHas('lease.unit', function ($q) use ($user) {
            if ($user->hasRole('landlord')) {
                $q->whereIn('property_id', $user->landlord?->properties()->pluck('id') ?? collect());
            }
        })->where('status', 'held')->sum('amount_paid');

        $openMaintenance = MaintenanceRequest::whereIn('status', ['submitted', 'in_progress'])
            ->when($user->hasRole('landlord'), function ($q) use ($user) {
                $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
                $q->whereHas('unit', fn ($u) => $u->whereIn('property_id', $propertyIds));
            })
            ->count();

        return view('reports.index', compact('activeLeases', 'terminatingLeases', 'totalDepositsHeld', 'openMaintenance'));
    }

    public function show(string $id)
    {
        return view('placeholder', ['title' => 'Report Detail']);
    }
}
