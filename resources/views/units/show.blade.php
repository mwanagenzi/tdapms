<x-layouts::app :title="'Unit ' . $unit->unit_number">
    <flux:main class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">Unit {{ $unit->unit_number }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $unit->property->name }} · {{ strtoupper($unit->type) }} @if($unit->floor) · {{ $unit->floor }} floor @endif
                </flux:text>
            </div>
            <div class="flex gap-2">
                @if ($unit->isAvailable() && auth()->user()->can('create leases'))
                <flux:button href="{{ route('leases.create') }}?unit_id={{ $unit->id }}" size="sm" variant="primary" icon="plus" wire:navigate>
                    {{ __('Create Lease') }}
                </flux:button>
                @endif
                @can('update units')
                <flux:button href="{{ route('units.edit', $unit) }}" size="sm" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card icon="home" label="{{ __('Status') }}" value="{{ ucfirst($unit->status) }}" :color="match($unit->status) { 'available' => 'green', 'occupied' => 'blue', default => 'orange' }" />
            <x-stat-card icon="maximize-2" label="{{ __('Size') }}" value="{{ $unit->size_sqft ? number_format($unit->size_sqft) . ' sqft' : '—' }}" color="zinc" />
            <x-stat-card icon="file-text" label="{{ __('Total Leases') }}" value="{{ $unit->leases->count() }}" color="indigo" />
        </div>

        {{-- Active Lease --}}
        @if ($unit->activeLease)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Active Lease') }}</flux:heading>
                <flux:badge color="blue">{{ ucfirst($unit->activeLease->status) }}</flux:badge>
            </div>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                <div><span class="text-zinc-500">Tenant:</span> <span class="font-medium">{{ $unit->activeLease->tenant->user->name }}</span></div>
                <div><span class="text-zinc-500">Rent:</span> <span class="font-medium">KES {{ number_format($unit->activeLease->monthly_rent, 2) }}</span></div>
                <div><span class="text-zinc-500">Deposit:</span> <span class="font-medium">KES {{ number_format($unit->activeLease->deposit_amount, 2) }}</span></div>
                <div><span class="text-zinc-500">Start:</span> {{ $unit->activeLease->start_date->format('d M Y') }}</div>
                @if ($unit->activeLease->end_date)
                <div><span class="text-zinc-500">End:</span> {{ $unit->activeLease->end_date->format('d M Y') }}</div>
                @endif
                <div>
                    <flux:button href="{{ route('leases.show', $unit->activeLease) }}" size="xs" wire:navigate>{{ __('View Lease') }}</flux:button>
                </div>
            </div>
        </div>
        @endif

        {{-- Lease History --}}
        @if ($unit->leases->count())
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <flux:heading size="sm">{{ __('Lease History') }}</flux:heading>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <th class="px-5 py-3 text-left font-medium text-zinc-500">Tenant</th>
                        <th class="px-5 py-3 text-left font-medium text-zinc-500">Start</th>
                        <th class="px-5 py-3 text-left font-medium text-zinc-500">End</th>
                        <th class="px-5 py-3 text-left font-medium text-zinc-500">Rent</th>
                        <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($unit->leases as $lease)
                    <tr>
                        <td class="px-5 py-3">{{ $lease->tenant->user->name }}</td>
                        <td class="px-5 py-3 text-zinc-500">{{ $lease->start_date->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-zinc-500">{{ $lease->end_date?->format('d M Y') ?? 'Open' }}</td>
                        <td class="px-5 py-3">KES {{ number_format($lease->monthly_rent, 2) }}</td>
                        <td class="px-5 py-3">
                            <flux:badge :color="$lease->status_badge['color']" size="sm">{{ $lease->status_badge['label'] }}</flux:badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </flux:main>
</x-layouts::app>
