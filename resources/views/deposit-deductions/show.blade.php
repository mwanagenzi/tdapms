<x-layouts::app :title="__('Deduction Details')">
    <flux:main class="mx-auto w-1/2 space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">Deposit Deduction</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $depositDeduction->lease->tenant->user->name }} ·
                    {{ $depositDeduction->lease->unit->unit_number }} ({{ $depositDeduction->lease->unit->property->name }})
                </flux:text>
            </div>
            <flux:badge :color="$depositDeduction->status_badge['color']" size="lg">
                {{ $depositDeduction->status_badge['label'] }}
            </flux:badge>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <div>
                <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-400">Reason</flux:text>
                <p class="mt-1 font-medium">{{ $depositDeduction->reason }}</p>
            </div>
            @if ($depositDeduction->description)
            <div>
                <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-400">Description</flux:text>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $depositDeduction->description }}</p>
            </div>
            @endif
            <div>
                <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-400">Amount</flux:text>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">KES {{ number_format($depositDeduction->amount, 2) }}</p>
            </div>
            @if ($depositDeduction->inspectionReportItem)
            <div>
                <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-400">Linked Inspection Item</flux:text>
                <p class="mt-1 text-sm">
                    {{ $depositDeduction->inspectionReportItem->item_name }} ({{ $depositDeduction->inspectionReportItem->category }})
                    — <flux:badge :color="$depositDeduction->inspectionReportItem->condition_badge['color']" size="sm">
                        {{ $depositDeduction->inspectionReportItem->condition_badge['label'] }}
                    </flux:badge>
                </p>
            </div>
            @endif
        </div>

        {{-- Review section (landlord only) --}}
        @if ($depositDeduction->isPending())
        @can('approve deposit deductions')
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 space-y-4">
            <flux:heading size="sm">Review This Deduction</flux:heading>

            <form method="POST" action="{{ route('deposit-deductions.approve', $depositDeduction) }}" class="space-y-3">
                @csrf
                <flux:field>
                    <flux:label aside="Optional">Notes</flux:label>
                    <flux:textarea name="review_notes" rows="2" placeholder="Optional approval notes…"></flux:textarea>
                </flux:field>
                <flux:button type="submit" variant="primary" icon="check">Approve Deduction</flux:button>
            </form>

            <form method="POST" action="{{ route('deposit-deductions.reject', $depositDeduction) }}" class="space-y-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:field>
                    <flux:label>Rejection Reason <span class="ms-1.5 text-xs text-red-500">Required</span></flux:label>
                    <flux:textarea name="review_notes" rows="2" required placeholder="Explain why this deduction is rejected…"></flux:textarea>
                </flux:field>
                <flux:button type="submit" variant="ghost" class="text-red-600" icon="x-mark">Reject Deduction</flux:button>
            </form>
        </div>
        @endcan
        @endif

        {{-- Review result --}}
        @if (! $depositDeduction->isPending())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-3">Review Result</flux:heading>
            <p class="text-sm text-zinc-500">Reviewed by: <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $depositDeduction->reviewedBy?->name }}</span>
                on {{ $depositDeduction->reviewed_at?->format('d M Y H:i') }}</p>
            @if ($depositDeduction->review_notes)
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $depositDeduction->review_notes }}</p>
            @endif
        </div>
        @endif

    </flux:main>
</x-layouts::app>
