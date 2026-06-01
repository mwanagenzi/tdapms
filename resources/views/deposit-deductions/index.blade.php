<x-layouts::app :title="__('Deposit Deductions')">
    <flux:main class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Deposit Deductions') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Deductions to be applied to tenant deposits at move-out.') }}</flux:text>
            </div>
            @can('create deposit deductions')
            <flux:button href="{{ route('deposit-deductions.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Record Deduction') }}
            </flux:button>
            @endcan
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
            <a href="{{ route('deposit-deductions.index') }}{{ $val ? '?status='.$val : '' }}"
               class="rounded-full border px-3 py-1 text-xs font-medium transition
                      {{ $statusFilter === $val || ($statusFilter === null && $val === '') ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 dark:border-zinc-700' }}">
                {{ $label }}
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
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Reason</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Amount</th>
                            <th class="px-5 py-3 text-left font-medium text-zinc-500">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($deductions as $deduction)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $deduction->lease->tenant->user->name }}</td>
                            <td class="px-5 py-3 text-zinc-500">{{ $deduction->lease->unit->unit_number }} · {{ $deduction->lease->unit->property->name }}</td>
                            <td class="px-5 py-3">{{ $deduction->reason }}</td>
                            <td class="px-5 py-3 font-medium">KES {{ number_format($deduction->amount, 2) }}</td>
                            <td class="px-5 py-3">
                                <flux:badge :color="$deduction->status_badge['color']" size="sm">
                                    {{ $deduction->status_badge['label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <flux:button href="{{ route('deposit-deductions.show', $deduction) }}" size="xs" variant="ghost" wire:navigate>View</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-zinc-400">No deductions recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $deductions->links() }}</div>
    </flux:main>
</x-layouts::app>
