<x-layouts::app :title="__('Dashboard')">
    <flux:main class="space-y-6">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    @role('super_admin'){{ __('System overview — all landlords and properties.') }}@endrole
                    @role('landlord'){{ __('Your properties, deposits, and escrow at a glance.') }}@endrole
                    @role('caretaker'){{ __('Your assigned properties and pending tasks.') }}@endrole
                </flux:text>
            </div>
        </div>

        {{-- Super Admin Stats --}}
        @role('super_admin')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building-office-2" label="{{ __('Total Landlords') }}"   value="{{ $stats['total_landlords'] }}"  color="blue" />
            <x-stat-card icon="building-office"   label="{{ __('Total Properties') }}"  value="{{ $stats['total_properties'] }}" color="indigo" />
            <x-stat-card icon="user-group"         label="{{ __('Active Tenants') }}"    value="{{ $stats['active_tenants'] }}"   color="green" />
            <x-stat-card icon="shield-check"       label="{{ __('Total Escrow (KES)') }}" value="KES {{ number_format($stats['total_escrow'], 2) }}" color="amber" />
        </div>
        @endrole

        {{-- Landlord Stats --}}
        @role('landlord')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building-office" label="{{ __('Properties') }}"        value="{{ $stats['properties'] }}"         color="blue" />
            <x-stat-card icon="user-group"       label="{{ __('Active Tenants') }}"    value="{{ $stats['active_tenants'] }}"     color="green" />
            <x-stat-card icon="shield-check"     label="{{ __('Escrow (KES)') }}"      value="KES {{ number_format($stats['escrow_balance'], 2) }}" color="amber" />
            <x-stat-card icon="clock"            label="{{ __('Pending Deductions') }}" value="{{ $stats['pending_deductions'] }}" color="orange" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-stat-card icon="check-circle" label="{{ __('Deposits Held') }}"           value="{{ $stats['deposits_held'] }}"      color="emerald" />
            <x-stat-card icon="arrow-path"   label="{{ __('Pending Refunds') }}"          value="{{ $stats['pending_refunds'] }}"    color="sky" />
            <x-stat-card icon="chart-bar"    label="{{ __('Refunds This Month (KES)') }}" value="KES {{ number_format($stats['refunds_this_month'], 2) }}" color="violet" />
        </div>
        @endrole

        {{-- Caretaker Stats --}}
        @role('caretaker')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="building-office"          label="{{ __('My Properties') }}"      value="{{ $stats['my_properties'] }}"      color="blue" />
            <x-stat-card icon="user-group"               label="{{ __('Active Tenants') }}"      value="{{ $stats['active_tenants'] }}"     color="green" />
            <x-stat-card icon="clipboard-document-check" label="{{ __('Pending Inspections') }}" value="{{ $stats['pending_inspections'] }}" color="amber" />
            <x-stat-card icon="wrench-screwdriver"       label="{{ __('Open Maintenance') }}"    value="{{ $stats['open_maintenance'] }}"   color="orange" />
        </div>
        @endrole

        {{-- Recent Activity --}}
        <div class="grid gap-4 lg:grid-cols-2">

            {{-- Recent Leases --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Recent Leases') }}</flux:heading>
                    <flux:button href="{{ route('leases.index') }}" size="xs" variant="ghost" wire:navigate>{{ __('View all') }}</flux:button>
                </div>
                @if ($recentLeases->isEmpty())
                    <div class="flex h-24 items-center justify-center">
                        <flux:text class="text-zinc-400">{{ __('No leases yet.') }}</flux:text>
                    </div>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($recentLeases as $lease)
                        <div class="flex items-center justify-between py-2.5 text-sm">
                            <div>
                                <p class="font-medium text-zinc-800 dark:text-zinc-100">{{ $lease->tenant->user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $lease->unit->unit_number }} · {{ $lease->unit->property->name }}</p>
                            </div>
                            <flux:badge size="sm" :color="match($lease->status) {
                                'active'      => 'green',
                                'terminating' => 'amber',
                                'terminated'  => 'zinc',
                                default       => 'blue',
                            }">{{ ucfirst($lease->status) }}</flux:badge>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Deposit Activity --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Recent Deposit Activity') }}</flux:heading>
                    <flux:button href="{{ route('deposits.index') }}" size="xs" variant="ghost" wire:navigate>{{ __('View all') }}</flux:button>
                </div>
                @if ($recentDeposits->isEmpty())
                    <div class="flex h-24 items-center justify-center">
                        <flux:text class="text-zinc-400">{{ __('No deposit activity yet.') }}</flux:text>
                    </div>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($recentDeposits as $deposit)
                        <div class="flex items-center justify-between py-2.5 text-sm">
                            <div>
                                <p class="font-medium text-zinc-800 dark:text-zinc-100">{{ $deposit->lease->tenant->user->name }}</p>
                                <p class="text-xs text-zinc-500">KES {{ number_format($deposit->amount_paid, 2) }} / {{ number_format($deposit->amount_required, 2) }}</p>
                            </div>
                            <flux:badge size="sm" :color="match($deposit->status) {
                                'held'           => 'green',
                                'partially_paid' => 'amber',
                                'refunding'      => 'sky',
                                'refunded'       => 'zinc',
                                default          => 'blue',
                            }">{{ ucfirst(str_replace('_', ' ', $deposit->status)) }}</flux:badge>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </flux:main>
</x-layouts::app>
