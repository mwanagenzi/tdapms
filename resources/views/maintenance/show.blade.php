<x-layouts::app :title="$maintenance->title">
    <flux:main class="space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $maintenance->title }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $maintenance->tenant->user->name }} · {{ $maintenance->unit->unit_number }} ({{ $maintenance->unit->property->name }})
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge :color="$maintenance->priority_badge['color']">{{ $maintenance->priority_badge['label'] }}</flux:badge>
                <flux:badge :color="$maintenance->status_badge['color']">{{ $maintenance->status_badge['label'] }}</flux:badge>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Request details --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">Request Details</flux:heading>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $maintenance->description }}</p>
                <p class="mt-3 text-xs text-zinc-400">Submitted: {{ $maintenance->created_at->format('d M Y H:i') }}</p>
            </div>

            {{-- Update status (caretaker) --}}
            @can('update maintenance status')
            @if (! in_array($maintenance->status, ['completed', 'rejected']))
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">Update Status</flux:heading>
                <form method="POST" action="{{ route('maintenance.update-status', $maintenance) }}" class="space-y-3">
                    @csrf
                    <flux:field>
                        <flux:label>New Status</flux:label>
                        <flux:select name="status">
                            <flux:option value="in_progress">In Progress</flux:option>
                            <flux:option value="completed">Completed</flux:option>
                            <flux:option value="rejected">Rejected</flux:option>
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Notes</flux:label>
                        <flux:textarea name="notes" rows="2" required placeholder="Describe the action taken or reason for rejection…"></flux:textarea>
                    </flux:field>
                    <flux:button type="submit" variant="primary" size="sm">Update Status</flux:button>
                </form>
            </div>
            @endif
            @endcan
        </div>

        {{-- Update log --}}
        @if ($maintenance->updates->count())
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <flux:heading size="sm">Activity Log</flux:heading>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($maintenance->updates as $update)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:avatar :name="$update->updatedBy->name" size="xs" />
                            <span class="text-sm font-medium">{{ $update->updatedBy->name }}</span>
                            <flux:badge :color="$update->status === 'completed' ? 'green' : ($update->status === 'rejected' ? 'red' : 'blue')" size="sm">
                                {{ ucwords(str_replace('_', ' ', $update->status)) }}
                            </flux:badge>
                        </div>
                        <span class="text-xs text-zinc-400">{{ $update->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $update->notes }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </flux:main>
</x-layouts::app>
