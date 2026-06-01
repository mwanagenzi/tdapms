<?php

namespace App\Services\Mpesa;

use App\Models\Deposit;
use App\Models\EscrowTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DarajaCallbackHandler
{
    // -------------------------------------------------------
    // STK Push callback — deposit collection result
    // -------------------------------------------------------

    public function handleStkCallback(array $payload): void
    {
        try {
            $body   = $payload['Body']['stkCallback'] ?? [];
            $code   = $body['ResultCode'] ?? -1;
            $reqId  = $body['CheckoutRequestID'] ?? null;

            if (! $reqId) {
                Log::warning('STK callback missing CheckoutRequestID', $payload);
                return;
            }

            $transaction = EscrowTransaction::where('mpesa_checkout_request_id', $reqId)->first();

            if (! $transaction) {
                Log::warning("STK callback: no EscrowTransaction found for CheckoutRequestID {$reqId}");
                return;
            }

            if ($code !== 0) {
                // Payment failed or cancelled
                $transaction->update([
                    'status'   => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], $body),
                ]);
                Log::info("STK payment failed for transaction #{$transaction->id}", ['code' => $code]);
                return;
            }

            // Payment succeeded — extract M-Pesa reference
            $items     = $body['CallbackMetadata']['Item'] ?? [];
            $meta      = collect($items)->pluck('Value', 'Name');
            $mpesaRef  = $meta->get('MpesaReceiptNumber');
            $amount    = (float) $meta->get('Amount', 0);

            DB::transaction(function () use ($transaction, $mpesaRef, $amount, $body) {
                $transaction->update([
                    'status'           => 'completed',
                    'mpesa_reference'  => $mpesaRef,
                    'amount'           => $amount,
                    'metadata'         => array_merge($transaction->metadata ?? [], $body),
                    'completed_at'     => now(),
                ]);

                $deposit = $transaction->deposit;
                $deposit->increment('amount_paid', $amount);

                // Update deposit status
                if ($deposit->amount_paid >= $deposit->amount_required) {
                    $deposit->update(['status' => 'held']);

                    // Activate lease once deposit is fully paid
                    if ($deposit->lease->status === 'pending_deposit') {
                        $deposit->lease->update(['status' => 'active']);
                    }
                } else {
                    $deposit->update(['status' => 'partially_paid']);
                }

                Log::info("Deposit #{$deposit->id} updated after STK payment. Paid: {$deposit->amount_paid}/{$deposit->amount_required}");
            });

        } catch (\Throwable $e) {
            Log::error('STK callback processing error: ' . $e->getMessage(), ['payload' => $payload]);
        }
    }

    // -------------------------------------------------------
    // B2C callback — deposit refund result
    // -------------------------------------------------------

    public function handleB2cCallback(array $payload): void
    {
        try {
            $result   = $payload['Result'] ?? [];
            $code     = $result['ResultCode'] ?? -1;
            $convId   = $result['ConversationID'] ?? null;

            if (! $convId) {
                Log::warning('B2C callback missing ConversationID', $payload);
                return;
            }

            // Find transaction by conversation_id stored in metadata
            $transaction = EscrowTransaction::whereJsonContains('metadata->ConversationID', $convId)->first();

            if (! $transaction) {
                Log::warning("B2C callback: no EscrowTransaction found for ConversationID {$convId}");
                return;
            }

            if ($code !== 0) {
                $transaction->update([
                    'status'   => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], $result),
                ]);
                Log::warning("B2C refund failed for transaction #{$transaction->id}", ['code' => $code]);

                // Revert deposit status to 'held'
                $transaction->deposit->update(['status' => 'held']);
                return;
            }

            $parameters = collect($result['ResultParameters']['ResultParameter'] ?? [])->pluck('Value', 'Key');
            $mpesaRef   = $parameters->get('TransactionReceipt');

            DB::transaction(function () use ($transaction, $mpesaRef, $result) {
                $transaction->update([
                    'status'          => 'completed',
                    'mpesa_reference' => $mpesaRef,
                    'metadata'        => array_merge($transaction->metadata ?? [], $result),
                    'completed_at'    => now(),
                ]);

                $deposit = $transaction->deposit;
                $deposit->update([
                    'status'      => 'refunded',
                    'refunded_at' => now(),
                ]);

                Log::info("Deposit #{$deposit->id} marked as refunded. MPESA Ref: {$mpesaRef}");
            });

        } catch (\Throwable $e) {
            Log::error('B2C callback processing error: ' . $e->getMessage(), ['payload' => $payload]);
        }
    }
}
