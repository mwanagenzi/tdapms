<x-layouts::app :title="'Lease — ' . $lease->tenant->user->name">
    <flux:main class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">Lease Agreement</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $lease->tenant->user->name }} · {{ $lease->unit->unit_number }} ({{ $lease->unit->property->name }})
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge :color="$lease->status_badge['color']" size="lg">{{ $lease->status_badge['label'] }}</flux:badge>
                @if (!in_array($lease->status, ['terminated']))
                @can('update leases')
                <flux:button href="{{ route('leases.edit', $lease) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
                @endcan
                @endif
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        {{-- Summary --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="banknotes" label="Monthly Rent" value="KES {{ number_format($lease->monthly_rent, 2) }}" color="blue" />
            <x-stat-card icon="shield-check" label="Deposit Required" value="KES {{ number_format($lease->deposit_amount, 2) }}" color="amber" />
            <x-stat-card icon="calendar" label="Start Date" value="{{ $lease->start_date->format('d M Y') }}" color="green" />
            <x-stat-card icon="calendar-days" label="End Date" value="{{ $lease->end_date?->format('d M Y') ?? 'Open-ended' }}" color="zinc" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Deposit Status --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Deposit & Escrow') }}</flux:heading>
                    @if ($lease->deposit)
                        <flux:badge :color="match($lease->deposit->status) {
                            'pending' => 'amber', 'partially_paid' => 'orange',
                            'held' => 'green', 'refunding' => 'sky', 'refunded' => 'zinc', default => 'zinc'
                        }">{{ ucfirst(str_replace('_', ' ', $lease->deposit->status)) }}</flux:badge>
                    @endif
                </div>
                @if ($lease->deposit)
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Required</span>
                        <span class="font-medium">KES {{ number_format($lease->deposit->amount_required, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Paid</span>
                        <span class="font-semibold {{ $lease->deposit->amount_paid >= $lease->deposit->amount_required ? 'text-green-600' : 'text-amber-600' }}">
                            KES {{ number_format($lease->deposit->amount_paid, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-zinc-100 pt-2 dark:border-zinc-800">
                        <span class="text-zinc-500">Outstanding</span>
                        <span class="font-medium">KES {{ number_format(max(0, $lease->deposit->amount_required - $lease->deposit->amount_paid), 2) }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <flux:button href="{{ route('deposits.show', $lease->deposit) }}" size="sm" wire:navigate>
                        {{ __('View Deposit Details') }}
                    </flux:button>
                </div>
                @endif
            </div>

            {{-- Inspection Reports --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Inspection Reports') }}</flux:heading>
                    @can('create inspections')
                    <flux:button href="{{ route('inspections.create') }}?lease_id={{ $lease->id }}" size="xs" icon="plus" wire:navigate>
                        {{ __('New') }}
                    </flux:button>
                    @endcan
                </div>
                @forelse ($lease->inspectionReports as $report)
                <div class="mt-2 flex items-center justify-between rounded-lg border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                    <div>
                        <p class="font-medium text-sm">{{ ucfirst($report->type === 'move_in' ? 'Move-In' : 'Move-Out') }}</p>
                        <p class="text-xs text-zinc-500">{{ $report->created_at->format('d M Y') }}</p>
                    </div>
                    <flux:badge :color="$report->status === 'completed' ? 'green' : 'amber'" size="sm">
                        {{ ucfirst($report->status) }}
                    </flux:badge>
                </div>
                @empty
                <flux:text class="mt-3 text-zinc-400 text-sm">{{ __('No inspection reports yet.') }}</flux:text>
                @endforelse
            </div>
        </div>

        {{-- Deposit Deductions --}}
        @if ($lease->depositDeductions->count())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-3">{{ __('Deposit Deductions') }}</flux:heading>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <th class="py-2 text-left font-medium text-zinc-500">Reason</th>
                        <th class="py-2 text-left font-medium text-zinc-500">Amount</th>
                        <th class="py-2 text-left font-medium text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($lease->depositDeductions as $deduction)
                    <tr>
                        <td class="py-2">{{ $deduction->reason }}</td>
                        <td class="py-2">KES {{ number_format($deduction->amount, 2) }}</td>
                        <td class="py-2">
                            <flux:badge :color="match($deduction->status) { 'approved' => 'green', 'rejected' => 'red', default => 'amber' }" size="sm">
                                {{ ucfirst($deduction->status) }}
                            </flux:badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </flux:main>
</x-layouts::app>
