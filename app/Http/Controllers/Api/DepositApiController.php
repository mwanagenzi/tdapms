<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepositApiController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $deposits = $tenant->leases()
            ->with([
                'deposit.escrowTransactions',
                'unit.property',
                'deposit.lease.depositDeductions',
            ])
            ->whereHas('deposit')
            ->get()
            ->map(fn ($lease) => $this->formatDeposit($lease->deposit));

        return response()->json(['data' => $deposits]);
    }

    public function show(Request $request, $depositId)
    {
        $tenant = $request->user()->tenant;

        $deposit = $tenant->leases()
            ->with([
                'deposit.escrowTransactions',
                'deposit.lease.unit.property',
                'deposit.lease.depositDeductions',
            ])
            ->whereHas('deposit', fn ($q) => $q->where('id', $depositId))
            ->first()
            ?->deposit;

        if (! $deposit) {
            return response()->json(['message' => 'Deposit not found.'], 404);
        }

        return response()->json(['data' => $this->formatDeposit($deposit, true)]);
    }

    private function formatDeposit($deposit, bool $detailed = false): array
    {
        $base = [
            'id'              => $deposit->id,
            'lease_id'        => $deposit->lease_id,
            'unit_number'     => $deposit->lease->unit->unit_number,
            'property_name'   => $deposit->lease->unit->property->name,
            'amount_required' => (float) $deposit->amount_required,
            'amount_paid'     => (float) $deposit->amount_paid,
            'outstanding'     => (float) $deposit->outstanding,
            'status'          => $deposit->status,
            'status_label'    => $deposit->status_badge['label'],
            'net_refund'      => (float) $deposit->net_refund_amount,
        ];

        if ($detailed) {
            $base['transactions'] = $deposit->escrowTransactions->map(fn ($tx) => [
                'id'              => $tx->id,
                'type'            => $tx->type,
                'amount'          => (float) $tx->amount,
                'status'          => $tx->status,
                'mpesa_reference' => $tx->mpesa_reference,
                'date'            => $tx->created_at->toISOString(),
            ]);

            $base['deductions'] = $deposit->lease->depositDeductions->map(fn ($d) => [
                'id'     => $d->id,
                'reason' => $d->reason,
                'amount' => (float) $d->amount,
                'status' => $d->status,
            ]);
        }

        return $base;
    }
}
