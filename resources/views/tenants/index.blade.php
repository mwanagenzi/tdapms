<x-layouts::app :title="__('Tenants')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Tenants') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All tenants in your properties.') }}</flux:text>
            </div>
            @can('create tenants')
            <flux:button href="{{ route('tenants.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Add Tenant') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Tenant') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Phone') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Lease Status') }}</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">{{ __('Account') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($tenants as $tenant)
                        <tr>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <flux:avatar :name="$tenant->user->name" size="sm" />
                                    <div>
                                        <p class="font-medium">{{ $tenant->user->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ $tenant->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-zinc-500">{{ $tenant->user->phone }}</td>
                            <td class="px-5 py-3">
                                @if ($tenant->activeLease)
                                    {{ $tenant->activeLease->unit->unit_number }} · {{ $tenant->activeLease->unit->property->name }}
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($tenant->activeLease)
                                    <flux:badge :color="$tenant->activeLease->status_badge['color']" size="sm">
                                        {{ $tenant->activeLease->status_badge['label'] }}
                                    </flux:badge>
                                @else
                                    <span class="text-zinc-400">No active lease</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$tenant->status === 'active' ? 'green' : 'zinc'" size="sm">
                                    {{ ucfirst($tenant->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('tenants.show', $tenant) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-zinc-400">{{ __('No tenants found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $tenants->links() }}</div>
    </flux:main>
</x-layouts::app>
