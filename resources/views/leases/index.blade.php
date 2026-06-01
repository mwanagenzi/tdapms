<x-layouts::app :title="__('Leases')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Leases') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All lease agreements across your properties.') }}</flux:text>
            </div>
            @can('create leases')
            <flux:button href="{{ route('leases.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Create Lease') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2">
            @php $statuses = ['', 'pending_deposit', 'active', 'terminating', 'terminated']; @endphp
            @foreach ($statuses as $s)
            <a href="{{ route('leases.index') }}{{ $s ? '?status='.$s : '' }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition
                      {{ $statusFilter === $s || ($statusFilter === null && $s === '') ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 dark:border-zinc-700' }}">
                {{ $s === '' ? 'All' : ucwords(str_replace('_', ' ', $s)) }}
            </a>
            @endforeach
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Tenant</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Unit</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Monthly Rent</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Deposit</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Start Date</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($leases as $lease)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $lease->tenant->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">
                                {{ $lease->unit->unit_number }} · {{ $lease->unit->property->name }}
                            </td>
                            <td class="px-5 py-3">KES {{ number_format($lease->monthly_rent, 2) }}</td>
                            <td class="px-5 py-3">KES {{ number_format($lease->deposit_amount, 2) }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $lease->start_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$lease->status_badge['color']" size="sm">
                                    {{ $lease->status_badge['label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('leases.show', $lease) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-zinc-400">No leases found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $leases->links() }}</div>
    </flux:main>
</x-layouts::app>
