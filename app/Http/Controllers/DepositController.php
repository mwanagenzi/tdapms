<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\EscrowTransaction;
use App\Services\Mpesa\DarajaService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function __construct(protected DarajaService $daraja) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Deposit::with(['lease.tenant.user', 'lease.unit.property']);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('lease.unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $statusFilter = $request->get('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $deposits = $query->latest()->paginate(20);

        $totalHeld = (clone $query)->where('status', 'held')->sum('amount_paid');

        return view('deposits.index', compact('deposits', 'statusFilter', 'totalHeld'));
    }

    public function create()
    {
        return view('placeholder', ['title' => 'Create Deposit']);
    }

    public function store(Request $request) {}

    public function show(Deposit $deposit)
    {
        $deposit->load([
            'lease.tenant.user',
            'lease.unit.property',
            'lease.depositDeductions',
            'escrowTransactions' => fn ($q) => $q->latest(),
        ]);

        return view('deposits.show', compact('deposit'));
    }

    public function edit(Deposit $deposit) {}

    public function update(Request $request, Deposit $deposit) {}

    public function destroy(Deposit $deposit) {}

    /**
     * Initiate STK Push to collect the deposit from the tenant.
     */
    public function initiateCollection(Request $request, Deposit $deposit)
    {
        if (! in_array($deposit->status, ['pending', 'partially_paid'])) {
            return back()->with('error', 'Deposit is not in a collectable state.');
        }

        $tenant = $deposit->lease->tenant;
        $phone  = $tenant->user->phone;

        if (empty($phone)) {
            return back()->with('error', 'Tenant has no phone number on file. Update the tenant first.');
        }

        $outstanding = $deposit->outstanding;

        if ($outstanding <= 0) {
            return back()->with('error', 'Deposit is already fully paid.');
        }

        // Create a pending EscrowTransaction first
        $transaction = EscrowTransaction::create([
            'deposit_id' => $deposit->id,
            'type'       => 'collection',
            'amount'     => $outstanding,
            'status'     => 'pending',
            'phone'      => $phone,
        ]);

        $result = $this->daraja->stkPush(
            phone: $phone,
            amount: $outstanding,
            accountRef: $deposit->lease->unit->unit_number,
            description: 'Security Deposit',
        );

        if ($result['success']) {
            $transaction->update([
                'mpesa_checkout_request_id' => $result['checkout_request_id'],
                'metadata' => ['merchant_request_id' => $result['merchant_request_id']],
            ]);

            return back()->with('success',
                "STK push sent to {$phone}. Amount: KES " . number_format($outstanding, 2) . ". " . $result['message']
            );
        }

        $transaction->update(['status' => 'cancelled']);

        return back()->with('error', 'STK Push failed: ' . $result['message']);
    }

    /**
     * Initiate B2C refund to the tenant after lease termination.
     */
    public function initiateRefund(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'held') {
            return back()->with('error', 'Only deposits with status "Held in Escrow" can be refunded.');
        }

        $netRefund = $deposit->net_refund_amount;

        if ($netRefund <= 0) {
            return back()->with('error', 'Net refund amount is zero after deductions. No refund to process.');
        }

        $tenant = $deposit->lease->tenant;
        $phone  = $tenant->user->phone;

        if (empty($phone)) {
            return back()->with('error', 'Tenant has no phone number. Update the tenant first.');
        }

        $deposit->update([
            'status'                => 'refunding',
            'refund_initiated_at'   => now(),
            'refund_initiated_by_id' => $request->user()->id,
        ]);

        $transaction = EscrowTransaction::create([
            'deposit_id' => $deposit->id,
            'type'       => 'refund',
            'amount'     => $netRefund,
            'status'     => 'pending',
            'phone'      => $phone,
        ]);

        $result = $this->daraja->b2cPayment(
            phone: $phone,
            amount: $netRefund,
            remarks: 'Deposit Refund - ' . $deposit->lease->unit->unit_number,
        );

        if ($result['success']) {
            $transaction->update([
                'metadata' => [
                    'ConversationID'              => $result['conversation_id'],
                    'OriginatorConversationID'    => $result['originator_id'],
                ],
            ]);

            return back()->with('success',
                "Refund of KES " . number_format($netRefund, 2) . " queued. {$result['message']}"
            );
        }

        $transaction->update(['status' => 'failed']);
        $deposit->update(['status' => 'held']); // Revert

        return back()->with('error', 'Refund failed: ' . $result['message']);
    }
}
