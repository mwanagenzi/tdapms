<x-layouts::app :title="__('Dashboard')">
    <flux:main class="space-y-6">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    @role('super_admin')
                        {{ __('System overview — all landlords and properties.') }}
                    @endrole
                    @role('landlord')
                        {{ __('Your properties, deposits, and escrow at a glance.') }}
                    @endrole
                    @role('caretaker')
                        {{ __('Your assigned properties and pending tasks.') }}
                    @endrole
                </flux:text>
            </div>
        </div>

        {{-- Super Admin Stats --}}
        @role('super_admin')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building-2" label="{{ __('Total Landlords') }}" value="—" color="blue" />
            <x-stat-card icon="building" label="{{ __('Total Properties') }}" value="—" color="indigo" />
            <x-stat-card icon="users" label="{{ __('Active Tenants') }}" value="—" color="green" />
            <x-stat-card icon="shield" label="{{ __('Total Escrow (KES)') }}" value="—" color="amber" />
        </div>
        @endrole

        {{-- Landlord Stats --}}
        @role('landlord')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building" label="{{ __('Properties') }}" value="—" color="blue" />
            <x-stat-card icon="users" label="{{ __('Active Tenants') }}" value="—" color="green" />
            <x-stat-card icon="shield" label="{{ __('Escrow Balance (KES)') }}" value="—" color="amber" />
            <x-stat-card icon="clock" label="{{ __('Pending Deductions') }}" value="—" color="orange" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-stat-card icon="check-circle" label="{{ __('Deposits Held') }}" value="—" color="emerald" />
            <x-stat-card icon="rotate-ccw" label="{{ __('Pending Refunds') }}" value="—" color="sky" />
            <x-stat-card icon="bar-chart-2" label="{{ __('Refunds This Month (KES)') }}" value="—" color="violet" />
        </div>
        @endrole

        {{-- Caretaker Stats --}}
        @role('caretaker')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building" label="{{ __('My Properties') }}" value="—" color="blue" />
            <x-stat-card icon="users" label="{{ __('Active Tenants') }}" value="—" color="green" />
            <x-stat-card icon="clipboard-check" label="{{ __('Pending Inspections') }}" value="—" color="amber" />
            <x-stat-card icon="wrench" label="{{ __('Open Maintenance') }}" value="—" color="orange" />
        </div>
        @endrole

        {{-- Recent activity placeholder --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Recent Leases') }}</flux:heading>
                <div class="flex h-32 items-center justify-center">
                    <flux:text class="text-zinc-400">{{ __('Data will appear here once leases are created.') }}</flux:text>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Recent Deposit Activity') }}</flux:heading>
                <div class="flex h-32 items-center justify-center">
                    <flux:text class="text-zinc-400">{{ __('Deposit events will appear here.') }}</flux:text>
                </div>
            </div>
        </div>

    </flux:main>
</x-layouts::app>
