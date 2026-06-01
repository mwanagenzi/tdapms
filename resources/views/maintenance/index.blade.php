<x-layouts::app :title="__('Maintenance Requests')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Maintenance Requests') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Repair and maintenance requests from tenants.') }}</flux:text>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'All', 'submitted' => 'Submitted', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $val => $label)
            <a href="{{ route('maintenance.index') }}{{ $val ? '?status='.$val : '' }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition
                      {{ $statusFilter === $val || ($statusFilter === null && $val === '') ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 dark:border-zinc-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Title</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Tenant</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Unit</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Priority</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Date</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($requests as $request)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $request->title }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $request->tenant->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $request->unit->unit_number }} · {{ $request->unit->property->name }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$request->priority_badge['color']" size="sm">
                                    {{ $request->priority_badge['label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$request->status_badge['color']" size="sm">
                                    {{ $request->status_badge['label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-zinc-500">{{ $request->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('maintenance.show', $request) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-zinc-400">No maintenance requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $requests->links() }}</div>
    </flux:main>
</x-layouts::app>
