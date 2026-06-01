<x-layouts::app :title="$tenant->user->name">
    <flux:main class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:avatar :name="$tenant->user->name" size="lg" />
                <div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="xl">{{ $tenant->user->name }}</flux:heading>
                        <flux:badge :color="$tenant->status === 'active' ? 'green' : 'zinc'">{{ ucfirst($tenant->status) }}</flux:badge>
                    </div>
                    <flux:text class="text-zinc-500">{{ $tenant->user->email }} · {{ $tenant->user->phone }}</flux:text>
                </div>
            </div>
            <div class="flex gap-2">
                @can('create leases')
                @if (!$tenant->activeLease)
                <flux:button href="{{ route('leases.create') }}?tenant_id={{ $tenant->id }}" size="sm" variant="primary" icon="file-text" wire:navigate>
                    {{ __('Create Lease') }}
                </flux:button>
                @endif
                @endcan
                @can('update tenants')
                <flux:button href="{{ route('tenants.edit', $tenant) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Active Lease Card --}}
        @if ($tenant->activeLease)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Current Lease') }}</flux:heading>
                <flux:badge :color="$tenant->activeLease->status_badge['color']">
                    {{ $tenant->activeLease->status_badge['label'] }}
                </flux:badge>
            </div>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                <div><span class="text-zinc-500">Unit:</span> <span class="font-medium">{{ $tenant->activeLease->unit->unit_number }} — {{ $tenant->activeLease->unit->property->name }}</span></div>
                <div><span class="text-zinc-500">Monthly Rent:</span> <span class="font-medium">KES {{ number_format($tenant->activeLease->monthly_rent, 2) }}</span></div>
                <div><span class="text-zinc-500">Deposit:</span> <span class="font-medium">KES {{ number_format($tenant->activeLease->deposit_amount, 2) }}</span></div>
            </div>
            <div class="mt-3">
                <flux:button href="{{ route('leases.show', $tenant->activeLease) }}" size="sm" wire:navigate>
                    {{ __('View Lease Details') }}
                </flux:button>
            </div>
        </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            {{-- Personal Details --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Details') }}</flux:heading>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">ID Number</dt>
                        <dd>{{ $tenant->id_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Emergency Contact</dt>
                        <dd>{{ $tenant->emergency_contact_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Emergency Phone</dt>
                        <dd>{{ $tenant->emergency_contact_phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Lease History --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Lease History') }}</flux:heading>
                @forelse ($tenant->leases as $lease)
                <div class="mb-2 flex items-center justify-between rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800">
                    <div class="text-sm">
                        <p class="font-medium">{{ $lease->unit->unit_number }} — {{ $lease->unit->property->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $lease->start_date->format('d M Y') }}</p>
                    </div>
                    <flux:badge :color="$lease->status_badge['color']" size="sm">{{ $lease->status_badge['label'] }}</flux:badge>
                </div>
                @empty
                <flux:text class="text-zinc-400">No leases yet.</flux:text>
                @endforelse
            </div>
        </div>
    </flux:main>
</x-layouts::app>
