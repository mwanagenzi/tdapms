<x-layouts::app :title="$landlord->user->name">
    <flux:main class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:avatar :name="$landlord->user->name" size="lg" />
                <div>
                    <flux:heading size="xl">{{ $landlord->user->name }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ $landlord->user->email }} · {{ $landlord->user->phone }}</flux:text>
                    @if ($landlord->company_name)
                    <p class="text-sm text-zinc-500">{{ $landlord->company_name }}</p>
                    @endif
                </div>
            </div>
            <flux:button href="{{ route('landlords.edit', $landlord) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card icon="building" label="Properties" value="{{ $landlord->properties->count() }}" color="blue" />
            <x-stat-card icon="home" label="Total Units" value="{{ $landlord->properties->sum(fn($p) => $p->units->count()) }}" color="indigo" />
            <x-stat-card icon="users" label="Occupied Units" value="{{ $landlord->properties->sum(fn($p) => $p->units->where('status', 'occupied')->count()) }}" color="green" />
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <flux:heading size="sm">Properties</flux:heading>
                <flux:button href="{{ route('properties.create') }}" size="xs" icon="plus" wire:navigate>Add Property</flux:button>
            </div>
            @forelse ($landlord->properties as $property)
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 last:border-0 dark:border-zinc-800">
                <div>
                    <p class="font-medium">{{ $property->name }}</p>
                    <p class="text-sm text-zinc-500">{{ $property->city }} · {{ $property->units->count() }} units</p>
                </div>
                <flux:button href="{{ route('properties.show', $property) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
            </div>
            @empty
            <p class="px-5 py-6 text-center text-sm text-zinc-400">No properties yet.</p>
            @endforelse
        </div>
    </flux:main>
</x-layouts::app>
