<x-layouts::app :title="__('Units')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Units') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All rental units across your properties.') }}</flux:text>
            </div>
            @can('create units')
            <flux:button href="{{ route('units.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Add Unit') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Filter --}}
        <form method="GET" class="flex gap-3">
            <flux:select name="property_id" class="w-56">
                <flux:option value="">{{ __('All Properties') }}</flux:option>
                @foreach ($properties as $property)
                    <flux:option value="{{ $property->id }}" :selected="$propertyFilter == $property->id">{{ $property->name }}</flux:option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="ghost">{{ __('Filter') }}</flux:button>
        </form>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Property') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Type') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Current Tenant') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($units as $unit)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $unit->unit_number }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $unit->property->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ strtoupper($unit->type) }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="match($unit->status) { 'available' => 'green', 'occupied' => 'blue', default => 'orange' }" size="sm">
                                    {{ ucfirst($unit->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3">{{ $unit->activeLease?->tenant->user->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('units.show', $unit) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-zinc-400">{{ __('No units found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $units->links() }}</div>
    </flux:main>
</x-layouts::app>
