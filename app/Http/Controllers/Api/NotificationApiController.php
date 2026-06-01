<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantNotification;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        $notifications = TenantNotification::where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(30);

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'data'       => $n->data,
                'read'       => $n->isRead(),
                'created_at' => $n->created_at->toISOString(),
            ]),
            'unread_count' => TenantNotification::where('tenant_id', $tenant->id)->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, TenantNotification $notification)
    {
        if ($notification->tenant_id !== $request->user()->tenant?->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'Tenant profile not found.'], 404);
        }

        TenantNotification::where('tenant_id', $tenant->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
