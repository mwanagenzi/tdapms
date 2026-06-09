<x-layouts::app :title="__('Properties')">
    <flux:main class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Properties') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All properties under your management.') }}</flux:text>
            </div>
            @can('create properties')
            <flux:button href="{{ route('properties.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Add Property') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($properties as $property)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="base">{{ $property->name }}</flux:heading>
                        <flux:text class="mt-0.5 text-sm text-zinc-500">{{ $property->city }}</flux:text>
                    </div>
                    <flux:badge :color="$property->type === 'apartment' ? 'blue' : ($property->type === 'commercial' ? 'amber' : 'violet')">
                        {{ ucfirst($property->type) }}
                    </flux:badge>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-800">
                        <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $property->units->count() }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Total') }}</p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-2 dark:bg-green-950">
                        <p class="text-lg font-bold text-green-700 dark:text-green-400">{{ $property->units->where('status', 'available')->count() }}</p>
                        <p class="text-xs text-green-600 dark:text-green-500">{{ __('Available') }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-950">
                        <p class="text-lg font-bold text-blue-700 dark:text-blue-400">{{ $property->units->where('status', 'occupied')->count() }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-500">{{ __('Occupied') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <flux:button href="{{ route('properties.show', $property) }}" size="sm" wire:navigate class="flex-1">
                        {{ __('View') }}
                    </flux:button>
                    @can('update properties')
                    <flux:button href="{{ route('properties.edit', $property) }}" size="sm" variant="ghost" wire:navigate>
                        {{ __('Edit') }}
                    </flux:button>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-3 flex flex-col items-center justify-center py-16 text-center">
                <flux:icon name="building-office" class="mb-3 size-10 text-zinc-300" />
                <flux:heading>{{ __('No properties yet') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Add your first property to get started.') }}</flux:text>
                @can('create properties')
                <flux:button href="{{ route('properties.create') }}" variant="primary" class="mt-4" wire:navigate>
                    {{ __('Add Property') }}
                </flux:button>
                @endcan
            </div>
            @endforelse
        </div>

        <div>{{ $properties->links() }}</div>

    </flux:main>
</x-layouts::app>
