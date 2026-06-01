<x-layouts::app :title="__('Record Deduction')">
    <flux:main class="max-w-xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Record Deposit Deduction') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Document damage or costs to be deducted from the tenant deposit.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('deposit-deductions.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>{{ __('Lease') }}</flux:label>
                <flux:select name="lease_id" required>
                    <flux:option value="">{{ __('Select lease…') }}</flux:option>
                    @foreach ($leases as $lease)
                        <flux:option value="{{ $lease->id }}" :selected="old('lease_id') == $lease->id">
                            {{ $lease->tenant->user->name }} — {{ $lease->unit->unit_number }} ({{ $lease->unit->property->name }})
                        </flux:option>
                    @endforeach
                </flux:select>
                @error('lease_id') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Reason') }}</flux:label>
                <flux:input name="reason" value="{{ old('reason') }}"
                    placeholder="e.g. Broken window in bedroom" required />
                @error('reason') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }} <flux:label.suffix>{{ __('Optional') }}</flux:label.suffix></flux:label>
                <flux:textarea name="description" rows="3" placeholder="Detailed description of the damage or cost…">{{ old('description') }}</flux:textarea>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Amount (KES)') }}</flux:label>
                <flux:input name="amount" type="number" min="1" step="0.01" value="{{ old('amount') }}" required />
                @error('amount') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-400">
                {{ __('This deduction will be sent to the landlord for approval before it affects the net refund amount.') }}
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Record Deduction') }}</flux:button>
                <flux:button href="{{ route('deposit-deductions.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
