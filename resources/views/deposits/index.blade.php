<x-layouts::app :title="__('Deposits & Escrow')">
    <flux:main class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Deposits & Escrow') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('All tenant security deposits and escrow balances.') }}</flux:text>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        {{-- Total escrow balance --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card icon="shield" label="{{ __('Total Held in Escrow') }}" value="KES {{ number_format($totalHeld, 2) }}" color="green" />
            <x-stat-card icon="clock" label="{{ __('Pending Collection') }}" value="{{ $deposits->where('status', 'pending')->count() + $deposits->where('status', 'partially_paid')->count() }}" color="amber" />
            <x-stat-card icon="rotate-ccw" label="{{ __('Being Refunded') }}" value="{{ $deposits->where('status', 'refunding')->count() }}" color="sky" />
        </div>

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['', 'pending', 'partially_paid', 'held', 'refunding', 'refunded'] as $s)
            <a href="{{ route('deposits.index') }}{{ $s ? '?status='.$s : '' }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition
                      {{ $statusFilter === $s || ($statusFilter === null && $s === '') ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 dark:border-zinc-700' }}">
                {{ $s === '' ? 'All' : ucwords(str_replace('_', ' ', $s)) }}
            </a>
            @endforeach
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Tenant</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Unit</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Required</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Paid</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Outstanding</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($deposits as $deposit)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $deposit->lease->tenant->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">
                                {{ $deposit->lease->unit->unit_number }} · {{ $deposit->lease->unit->property->name }}
                            </td>
                            <td class="px-5 py-3">KES {{ number_format($deposit->amount_required, 2) }}</td>
                            <td class="px-5 py-3 {{ $deposit->is_fully_paid ? 'text-green-600 font-medium' : '' }}">
                                KES {{ number_format($deposit->amount_paid, 2) }}
                            </td>
                            <td class="px-5 py-3 {{ $deposit->outstanding > 0 ? 'text-amber-600 font-medium' : 'text-zinc-400' }}">
                                KES {{ number_format($deposit->outstanding, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$deposit->status_badge['color']" size="sm">
                                    {{ $deposit->status_badge['label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('deposits.show', $deposit) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-zinc-400">No deposits found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $deposits->links() }}</div>

    </flux:main>
</x-layouts::app>
