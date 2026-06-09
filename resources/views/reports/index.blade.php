<x-layouts::app :title="__('Reports')">
    <flux:main class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Reports & Analytics') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Overview of property performance and financials.') }}</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="document-text" label="{{ __('Active Leases') }}" value="{{ $activeLeases }}" color="green" />
            <x-stat-card icon="exclamation-circle" label="{{ __('Terminating Leases') }}" value="{{ $terminatingLeases }}" color="orange" />
            <x-stat-card icon="shield-check" label="{{ __('Escrow Held (KES)') }}" value="{{ number_format($totalDepositsHeld, 2) }}" color="blue" />
            <x-stat-card icon="wrench-screwdriver" label="{{ __('Open Maintenance') }}" value="{{ $openMaintenance }}" color="amber" />
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon name="chart-bar" class="mx-auto mb-3 size-12 text-zinc-300" />
            <flux:heading>{{ __('Detailed Reports') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Detailed charts and export functionality will be available in a future update.') }}</flux:text>
        </div>
    </flux:main>
</x-layouts::app>
