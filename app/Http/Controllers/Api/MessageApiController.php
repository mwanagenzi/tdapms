<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Lease;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Services\TenantNotificationService;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    /**
     * Tenant initiates a new conversation for one of their leases.
     * POST /api/messages   { lease_id, body }
     */
    public function create(Request $request)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $validated = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'body'     => ['required', 'string'],
        ]);

        // Make sure the lease belongs to this tenant
        $lease = Lease::where('id', $validated['lease_id'])
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $lease) {
            return response()->json(['message' => 'Lease not found for this tenant.'], 404);
        }

        $conversation = Conversation::firstOrCreate(
            ['context_type' => Lease::class, 'context_id' => $lease->id],
            ['subject' => "Lease: {$user->name} — {$lease->unit->unit_number}"]
        );

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $validated['body'],
        ]);

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'message' => [
                    'id'         => $message->id,
                    'body'       => $message->body,
                    'sender'     => ['id' => $user->id, 'name' => $user->name],
                    'is_mine'    => true,
                    'created_at' => $message->created_at->toISOString(),
                ],
            ],
        ], 201);
    }

    public function index(Request $request)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $leaseIds = $tenant->leases()->pluck('id');

        $conversations = Conversation::with(['latestMessage.sender'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $user->id)])
            ->where('context_type', \App\Models\Lease::class)
            ->whereIn('context_id', $leaseIds)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id'           => $c->id,
                'subject'      => $c->subject,
                'unread_count' => $c->unread_count,
                'last_message' => $c->latestMessage ? [
                    'body'       => $c->latestMessage->body,
                    'sender'     => $c->latestMessage->sender->name,
                    'date'       => $c->latestMessage->created_at->toISOString(),
                ] : null,
            ]);

        return response()->json(['data' => $conversations]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        $leaseIds = $tenant->leases()->pluck('id');

        if (! ($conversation->context_type === \App\Models\Lease::class && in_array($conversation->context_id, $leaseIds->toArray()))) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $messages = $conversation->messages()->with(['sender', 'attachments'])->latest()->paginate(30);

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => $messages->map(fn ($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'sender'      => ['id' => $m->sender->id, 'name' => $m->sender->name],
                'is_mine'     => $m->sender_id === $user->id,
                'read'        => $m->isRead(),
                'attachments' => $m->attachments->map(fn ($a) => ['url' => $a->url, 'filename' => $a->filename]),
                'created_at'  => $m->created_at->toISOString(),
            ]),
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        $leaseIds = $tenant->leases()->pluck('id');

        if (! ($conversation->context_type === \App\Models\Lease::class && in_array($conversation->context_id, $leaseIds->toArray()))) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $validated['body'],
        ]);

        return response()->json([
            'data' => [
                'id'         => $message->id,
                'body'       => $message->body,
                'sender'     => ['id' => $user->id, 'name' => $user->name],
                'is_mine'    => true,
                'created_at' => $message->created_at->toISOString(),
            ],
        ], 201);
    }
}
