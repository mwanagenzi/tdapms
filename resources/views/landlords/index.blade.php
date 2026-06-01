<x-layouts::app :title="__('Landlords')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Landlords') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All registered landlords in the TDAPS system.') }}</flux:text>
            </div>
            <flux:button href="{{ route('landlords.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Add Landlord') }}
            </flux:button>
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
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Name</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Email</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Phone</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Company</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Properties</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($landlords as $landlord)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $landlord->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $landlord->user->email }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $landlord->user->phone }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $landlord->company_name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $landlord->properties_count }}</td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('landlords.show', $landlord) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-zinc-400">No landlords registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $landlords->links() }}</div>
    </flux:main>
</x-layouts::app>
