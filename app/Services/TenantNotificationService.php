<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantNotification;

class TenantNotificationService
{
    /**
     * Send a notification to a tenant.
     */
    public function notify(
        Tenant $tenant,
        string $type,
        string $title,
        string $body,
        array $data = []
    ): TenantNotification {
        return TenantNotification::create([
            'tenant_id' => $tenant->id,
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'data'      => $data,
        ]);
    }

    // -------------------------------------------------------
    // Deposit events
    // -------------------------------------------------------

    public function depositCollectionInitiated(Tenant $tenant, float $amount, string $phone): void
    {
        $this->notify(
            $tenant,
            'deposit_collection_initiated',
            'Deposit Payment Requested',
            "An STK push of KES " . number_format($amount, 2) . " has been sent to {$phone}. Check your phone to complete payment.",
            ['amount' => $amount, 'phone' => $phone]
        );
    }

    public function depositReceived(Tenant $tenant, float $amountPaid, float $amountRequired, string $mpesaRef): void
    {
        $fullyPaid = $amountPaid >= $amountRequired;
        $this->notify(
            $tenant,
            'deposit_received',
            $fullyPaid ? 'Deposit Fully Paid' : 'Deposit Payment Received',
            $fullyPaid
                ? "Your deposit of KES " . number_format($amountRequired, 2) . " is fully paid and held in escrow. Ref: {$mpesaRef}."
                : "Payment of KES " . number_format($amountPaid, 2) . " received. Ref: {$mpesaRef}. Outstanding: KES " . number_format(max(0, $amountRequired - $amountPaid), 2) . ".",
            ['amount_paid' => $amountPaid, 'amount_required' => $amountRequired, 'mpesa_reference' => $mpesaRef]
        );
    }

    public function depositRefundInitiated(Tenant $tenant, float $amount): void
    {
        $this->notify(
            $tenant,
            'deposit_refund_initiated',
            'Deposit Refund Initiated',
            "A refund of KES " . number_format($amount, 2) . " has been initiated to your phone. It will arrive shortly.",
            ['amount' => $amount]
        );
    }

    public function depositRefunded(Tenant $tenant, float $amount, string $mpesaRef): void
    {
        $this->notify(
            $tenant,
            'deposit_refunded',
            'Deposit Refunded',
            "KES " . number_format($amount, 2) . " has been sent to your phone. MPESA Ref: {$mpesaRef}.",
            ['amount' => $amount, 'mpesa_reference' => $mpesaRef]
        );
    }

    // -------------------------------------------------------
    // Deduction events
    // -------------------------------------------------------

    public function deductionAdded(Tenant $tenant, string $reason, float $amount): void
    {
        $this->notify(
            $tenant,
            'deduction_added',
            'Deposit Deduction Recorded',
            "A deduction of KES " . number_format($amount, 2) . " has been recorded: {$reason}. It is pending landlord review.",
            ['reason' => $reason, 'amount' => $amount]
        );
    }

    public function deductionApproved(Tenant $tenant, string $reason, float $amount): void
    {
        $this->notify(
            $tenant,
            'deduction_approved',
            'Deduction Approved',
            "The deduction of KES " . number_format($amount, 2) . " ({$reason}) has been approved by the landlord.",
            ['reason' => $reason, 'amount' => $amount]
        );
    }

    public function deductionRejected(Tenant $tenant, string $reason, float $amount): void
    {
        $this->notify(
            $tenant,
            'deduction_rejected',
            'Deduction Rejected',
            "The deduction of KES " . number_format($amount, 2) . " ({$reason}) has been rejected by the landlord.",
            ['reason' => $reason, 'amount' => $amount]
        );
    }

    // -------------------------------------------------------
    // Maintenance events
    // -------------------------------------------------------

    public function maintenanceUpdate(Tenant $tenant, string $title, string $status, string $notes): void
    {
        $label = ucfirst(str_replace('_', ' ', $status));
        $this->notify(
            $tenant,
            'maintenance_update',
            "Maintenance Request: {$label}",
            "Your request \"{$title}\" has been updated to {$label}. Notes: {$notes}",
            ['title' => $title, 'status' => $status, 'notes' => $notes]
        );
    }

    // -------------------------------------------------------
    // Message events
    // -------------------------------------------------------

    public function newMessage(Tenant $tenant, string $senderName, string $subject): void
    {
        $this->notify(
            $tenant,
            'new_message',
            "New Message from {$senderName}",
            "You have a new message in conversation: \"{$subject}\".",
            ['sender_name' => $senderName, 'subject' => $subject]
        );
    }
}
