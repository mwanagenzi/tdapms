<x-layouts::app :title="__('Inspection Reports')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Inspection Reports') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Move-in and move-out property condition reports.') }}</flux:text>
            </div>
            @can('create inspections')
            <flux:button href="{{ route('inspections.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('New Report') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Type filter --}}
        <div class="flex gap-2">
            @foreach (['' => 'All', 'move_in' => 'Move-In', 'move_out' => 'Move-Out'] as $val => $label)
            <a href="{{ route('inspections.index') }}{{ $val ? '?type='.$val : '' }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition
                      {{ $typeFilter === $val || ($typeFilter === null && $val === '') ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 dark:border-zinc-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Type</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Tenant</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Unit</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Conducted By</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Date</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($reports as $report)
                        <tr>
                            <td class="px-5 py-3">
                                <flux:badge :color="$report->type === 'move_in' ? 'green' : 'orange'" size="sm">
                                    {{ $report->type_label }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 font-medium">{{ $report->lease->tenant->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $report->lease->unit->unit_number }} · {{ $report->lease->unit->property->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $report->conductedBy->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $report->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$report->status === 'completed' ? 'green' : 'amber'" size="sm">
                                    {{ ucfirst($report->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('inspections.show', $report) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-zinc-400">No inspection reports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $reports->links() }}</div>
    </flux:main>
</x-layouts::app>
