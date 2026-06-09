<x-layouts::app :title="'Deposit — ' . $deposit->lease->tenant->user->name">
    <flux:main class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">Security Deposit</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $deposit->lease->tenant->user->name }} ·
                    {{ $deposit->lease->unit->unit_number }} ({{ $deposit->lease->unit->property->name }})
                </flux:text>
            </div>
            <flux:badge :color="$deposit->status_badge['color']" size="lg">
                {{ $deposit->status_badge['label'] }}
            </flux:badge>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        {{-- Summary cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card icon="shield-check" label="Required" value="KES {{ number_format($deposit->amount_required, 2) }}" color="blue" />
            <x-stat-card icon="check-circle" label="Paid" value="KES {{ number_format($deposit->amount_paid, 2) }}" :color="$deposit->is_fully_paid ? 'green' : 'amber'" />
            <x-stat-card icon="exclamation-circle" label="Outstanding" value="KES {{ number_format($deposit->outstanding, 2) }}" :color="$deposit->outstanding > 0 ? 'orange' : 'zinc'" />
            <x-stat-card icon="receipt-percent" label="Net Refund" value="KES {{ number_format($deposit->net_refund_amount, 2) }}" color="sky" />
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3">
            @can('initiate deposit collection')
                @if (in_array($deposit->status, ['pending', 'partially_paid']))
                <form method="POST" action="{{ route('deposits.initiate-collection', $deposit) }}">
                    @csrf
                    <flux:button type="submit" variant="primary" icon="device-phone-mobile">
                        {{ __('Send STK Push') }}
                        <flux:badge size="sm" class="ml-1">KES {{ number_format($deposit->outstanding, 2) }}</flux:badge>
                    </flux:button>
                </form>
                @endif
            @endcan

            @can('initiate deposit refund')
                @if ($deposit->status === 'held')
                <form method="POST" action="{{ route('deposits.initiate-refund', $deposit) }}">
                    @csrf
                    <flux:button type="submit" variant="ghost" icon="paper-airplane">
                        {{ __('Initiate Refund') }}
                        <flux:badge size="sm" class="ml-1">KES {{ number_format($deposit->net_refund_amount, 2) }}</flux:badge>
                    </flux:button>
                </form>
                @endif
            @endcan
        </div>

        {{-- Deductions Summary --}}
        @if ($deposit->lease->depositDeductions->count())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">Deposit Deductions</flux:heading>
                <flux:text class="text-sm text-zinc-500">
                    Approved: KES {{ number_format($deposit->lease->depositDeductions->where('status', 'approved')->sum('amount'), 2) }}
                </flux:text>
            </div>
            <table class="mt-3 w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <th class="pb-2 text-left font-medium text-zinc-500">Reason</th>
                        <th class="pb-2 text-left font-medium text-zinc-500">Amount</th>
                        <th class="pb-2 text-left font-medium text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($deposit->lease->depositDeductions as $deduction)
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

        {{-- Escrow Transaction History --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <flux:heading size="sm">MPESA Transaction Log</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Date</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Type</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Amount</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">MPESA Ref</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Phone</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($deposit->escrowTransactions as $tx)
                        <tr>
                            <td class="px-5 py-3 text-zinc-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$tx->type === 'collection' ? 'green' : 'sky'" size="sm">
                                    {{ ucfirst($tx->type) }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 font-medium">KES {{ number_format($tx->amount, 2) }}</td>
                            <td class="px-5 py-3 font-mono text-xs">{{ $tx->mpesa_reference ?? '—' }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $tx->phone }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="match($tx->status) { 'completed' => 'green', 'failed' => 'red', 'cancelled' => 'zinc', default => 'amber' }" size="sm">
                                    {{ ucfirst($tx->status) }}
                                </flux:badge>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-zinc-400">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </flux:main>
</x-layouts::app>
