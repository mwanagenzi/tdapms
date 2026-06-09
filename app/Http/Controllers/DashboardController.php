<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Lease;
use App\Models\Deposit;
use App\Models\DepositDeduction;
use App\Models\InspectionReport;
use App\Models\MaintenanceRequest;
use App\Models\EscrowTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = [];

        if ($user->hasRole('super_admin')) {
            $stats = [
                'total_landlords'    => Landlord::count(),
                'total_properties'   => Property::count(),
                'active_tenants'     => Tenant::where('status', 'active')->count(),
                'total_escrow'       => Deposit::whereIn('status', ['held', 'refunding'])->sum('amount_paid'),
            ];
        } elseif ($user->hasRole('landlord')) {
            $landlord   = $user->landlord;
            $propertyIds = $landlord?->properties()->pluck('id') ?? collect();
            $unitIds     = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $leaseIds    = Lease::whereIn('unit_id', $unitIds)->where('status', 'active')->pluck('id');
            $depositIds  = Deposit::whereIn('lease_id', $leaseIds)->pluck('id');

            $stats = [
                'properties'         => $propertyIds->count(),
                'active_tenants'     => Tenant::whereHas('leases', fn($q) => $q->whereIn('unit_id', $unitIds))->where('status', 'active')->count(),
                'escrow_balance'     => Deposit::whereIn('lease_id', $leaseIds)->whereIn('status', ['held', 'refunding'])->sum('amount_paid'),
                'pending_deductions' => DepositDeduction::whereIn('deposit_id', $depositIds)->where('status', 'pending')->count(),
                'deposits_held'      => Deposit::whereIn('lease_id', $leaseIds)->where('status', 'held')->count(),
                'pending_refunds'    => Deposit::whereIn('lease_id', $leaseIds)->where('status', 'refunding')->count(),
                'refunds_this_month' => EscrowTransaction::whereIn('deposit_id', $depositIds)
                                            ->where('type', 'refund')
                                            ->where('status', 'completed')
                                            ->whereMonth('created_at', Carbon::now()->month)
                                            ->sum('amount'),
            ];
        } elseif ($user->hasRole('caretaker')) {
            $caretaker   = $user->caretaker;
            $propertyIds = $caretaker?->properties()->pluck('properties.id') ?? collect();
            $unitIds     = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');

            $stats = [
                'my_properties'       => $propertyIds->count(),
                'active_tenants'      => Tenant::whereHas('leases', fn($q) => $q->whereIn('unit_id', $unitIds))->where('status', 'active')->count(),
                'pending_inspections' => InspectionReport::whereHas('lease', fn($q) => $q->whereIn('unit_id', $unitIds))
                                            ->where('status', 'draft')->count(),
                'open_maintenance'    => MaintenanceRequest::whereIn('unit_id', $unitIds)
                                            ->whereIn('status', ['submitted', 'in_progress'])->count(),
            ];
        }

        // Recent leases (role-scoped)
        $recentLeases = $this->recentLeases($user);

        // Recent deposit events
        $recentDeposits = $this->recentDeposits($user);

        return view('dashboard', compact('user', 'stats', 'recentLeases', 'recentDeposits'));
    }

    private function recentLeases($user)
    {
        $query = Lease::with(['tenant.user', 'unit.property'])->latest()->limit(5);

        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        } elseif ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        }

        return $query->get();
    }

    private function recentDeposits($user)
    {
        $query = Deposit::with(['lease.tenant.user', 'lease.unit.property'])->latest()->limit(5);

        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id');
            $query->whereIn('lease_id', $leaseIds);
        } elseif ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $unitIds = \App\Models\Unit::whereIn('property_id', $propertyIds)->pluck('id');
            $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id');
            $query->whereIn('lease_id', $leaseIds);
        }

        return $query->get();
    }
}
