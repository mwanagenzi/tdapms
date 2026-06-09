<x-layouts::app :title="__('Edit Deduction')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">Edit Deduction</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $depositDeduction->reason }}</flux:text>
        </div>

        <form method="POST" action="{{ route('deposit-deductions.update', $depositDeduction) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Reason') }}</flux:label>
                <flux:input name="reason" value="{{ old('reason', $depositDeduction->reason) }}" required />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea name="description" rows="3">{{ old('description', $depositDeduction->description) }}</flux:textarea>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Amount (KES)') }}</flux:label>
                <flux:input name="amount" type="number" min="1" step="0.01"
                    value="{{ old('amount', $depositDeduction->amount) }}" required />
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('deposit-deductions.show', $depositDeduction) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
