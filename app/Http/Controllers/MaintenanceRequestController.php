<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceUpdate;
use App\Models\Message;
use App\Models\Unit;
use App\Services\TenantNotificationService;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    public function __construct(protected TenantNotificationService $notifications) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = MaintenanceRequest::with(['unit.property', 'tenant.user', 'updates' => fn ($q) => $q->latest()->take(1)]);

        if ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker?->properties()->pluck('properties.id') ?? collect();
            $query->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        } elseif ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord?->properties()->pluck('id') ?? collect();
            $query->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds));
        }

        $statusFilter = $request->get('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $requests = $query->latest()->paginate(20);

        return view('maintenance.index', compact('requests', 'statusFilter'));
    }

    public function create(Request $request)
    {
        return view('placeholder', ['title' => 'New Maintenance Request']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'     => ['required', 'exists:units,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
        ]);

        $user = $request->user();
        $validated['tenant_id'] = $user->tenant->id;

        MaintenanceRequest::create($validated);

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Maintenance request submitted.');
    }

    public function show(MaintenanceRequest $maintenance)
    {
        $maintenance->load(['unit.property', 'tenant.user', 'updates.updatedBy']);

        return view('maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceRequest $maintenance)
    {
        return view('placeholder', ['title' => 'Edit Maintenance Request']);
    }

    public function update(Request $request, MaintenanceRequest $maintenance) {}

    public function destroy(MaintenanceRequest $maintenance) {}

    /**
     * Caretaker updates the status of a maintenance request.
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:submitted,in_progress,completed,rejected'],
            'notes'  => ['required', 'string'],
        ]);

        MaintenanceUpdate::create([
            'maintenance_request_id' => $maintenance->id,
            'updated_by_id'          => $request->user()->id,
            'status'                 => $validated['status'],
            'notes'                  => $validated['notes'],
        ]);

        $maintenance->update(['status' => $validated['status']]);

        // Post a message in the maintenance conversation thread
        $updater = $request->user();
        $conversation = Conversation::firstOrCreate(
            ['context_type' => MaintenanceRequest::class, 'context_id' => $maintenance->id],
            ['subject' => "Maintenance: {$maintenance->title}"]
        );

        $statusLabel = ucfirst(str_replace('_', ' ', $validated['status']));
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $updater->id,
            'body'            => "[Status updated to: {$statusLabel}]\n{$validated['notes']}",
        ]);

        // Notify the tenant
        $tenant = $maintenance->tenant;
        if ($tenant) {
            $this->notifications->maintenanceUpdate(
                $tenant,
                $maintenance->title,
                $validated['status'],
                $validated['notes']
            );
        }

        return back()->with('success', 'Status updated successfully.');
    }
}
