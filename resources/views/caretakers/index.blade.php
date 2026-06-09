<x-layouts::app :title="__('Caretakers')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Caretakers') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Manage staff assigned to your properties.') }}</flux:text>
            </div>
            @can('create caretakers')
            <flux:button href="{{ route('caretakers.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Add Caretaker') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($caretakers as $caretaker)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <flux:avatar :name="$caretaker->user->name" />
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $caretaker->user->name }}</p>
                        <p class="text-sm text-zinc-500">{{ $caretaker->user->phone }}</p>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach ($caretaker->properties->take(3) as $prop)
                    <flux:badge size="sm" color="blue">{{ $prop->name }}</flux:badge>
                    @endforeach
                    @if ($caretaker->properties->count() > 3)
                    <flux:badge size="sm" color="zinc">+{{ $caretaker->properties->count() - 3 }} more</flux:badge>
                    @endif
                </div>
                <div class="mt-3 flex gap-2">
                    <flux:button href="{{ route('caretakers.show', $caretaker) }}" size="sm" class="flex-1" wire:navigate>View</flux:button>
                    @can('update caretakers')
                    <flux:button href="{{ route('caretakers.edit', $caretaker) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
                    @endcan
                </div>
            </div>
            @empty
            <div class="col-span-3 flex flex-col items-center justify-center py-16 text-center">
                <flux:icon name="wrench-screwdriver" class="mb-3 size-10 text-zinc-300" />
                <flux:heading>{{ __('No caretakers yet') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Add your first caretaker to assign them to properties.') }}</flux:text>
            </div>
            @endforelse
        </div>
        <div>{{ $caretakers->links() }}</div>
    </flux:main>
</x-layouts::app>
