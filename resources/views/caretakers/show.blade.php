<x-layouts::app :title="$caretaker->user->name">
    <flux:main class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:avatar :name="$caretaker->user->name" size="lg" />
                <div>
                    <flux:heading size="xl">{{ $caretaker->user->name }}</flux:heading>
                    <flux:text class="text-zinc-500">{{ $caretaker->user->email }} · {{ $caretaker->user->phone }}</flux:text>
                </div>
            </div>
            @can('update caretakers')
            <flux:button href="{{ route('caretakers.edit', $caretaker) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Assigned Properties') }}</flux:heading>
            @forelse ($caretaker->properties as $property)
            <div class="mb-3 rounded-lg border border-zinc-100 p-4 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $property->name }}</p>
                        <p class="text-sm text-zinc-500">{{ $property->city }} · {{ $property->units->count() }} units</p>
                    </div>
                    <flux:button href="{{ route('properties.show', $property) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                </div>
            </div>
            @empty
            <flux:text class="text-zinc-400">{{ __('No properties assigned.') }}</flux:text>
            @endforelse
        </div>
    </flux:main>
</x-layouts::app>
