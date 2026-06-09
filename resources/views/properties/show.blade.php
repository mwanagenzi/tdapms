<x-layouts::app :title="$property->name">
    <flux:main class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ $property->name }}</flux:heading>
                    <flux:badge :color="$property->type === 'apartment' ? 'blue' : ($property->type === 'commercial' ? 'amber' : 'violet')">
                        {{ ucfirst($property->type) }}
                    </flux:badge>
                </div>
                <flux:text class="mt-1 text-zinc-500">{{ $property->address }}, {{ $property->city }}</flux:text>
            </div>
            <div class="flex gap-2">
                @can('create units')
                <flux:button href="{{ route('units.create') }}?property_id={{ $property->id }}" size="sm" icon="plus" wire:navigate>
                    {{ __('Add Unit') }}
                </flux:button>
                @endcan
                @can('update properties')
                <flux:button href="{{ route('properties.edit', $property) }}" size="sm" variant="ghost" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card icon="home" label="{{ __('Total Units') }}" value="{{ $property->units->count() }}" color="blue" />
            <x-stat-card icon="check-circle" label="{{ __('Available') }}" value="{{ $property->units->where('status', 'available')->count() }}" color="green" />
            <x-stat-card icon="user-group" label="{{ __('Occupied') }}" value="{{ $property->units->where('status', 'occupied')->count() }}" color="indigo" />
        </div>

        {{-- Caretakers --}}
        @if ($property->caretakers->count())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-3">{{ __('Assigned Caretakers') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach ($property->caretakers as $caretaker)
                <div class="flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:avatar size="xs" :name="$caretaker->user->name" />
                    <span class="text-sm font-medium">{{ $caretaker->user->name }}</span>
                    <span class="text-xs text-zinc-500">{{ $caretaker->user->phone }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Units Table --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Units') }}</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Type') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Floor') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Tenant') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($property->units as $unit)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $unit->unit_number }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ strtoupper($unit->type) }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $unit->floor ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $color = match($unit->status) {
                                        'available' => 'green',
                                        'occupied' => 'blue',
                                        'maintenance' => 'orange',
                                        default => 'zinc'
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ ucfirst($unit->status) }}</flux:badge>
                            </td>
                            <td class="px-5 py-3">
                                @if ($unit->activeLease)
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $unit->activeLease->tenant->user->name }}</span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('units.show', $unit) }}" size="xs" variant="ghost" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-400">
                                {{ __('No units added yet.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </flux:main>
</x-layouts::app>
