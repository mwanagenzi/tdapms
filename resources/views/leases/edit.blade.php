<x-layouts::app :title="__('Edit Lease')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">Edit Lease</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ $lease->tenant->user->name }} · {{ $lease->unit->unit_number }}
            </flux:text>
        </div>

        <form method="POST" action="{{ route('leases.update', $lease) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Start Date') }}</flux:label>
                    <flux:input name="start_date" type="date" value="{{ old('start_date', $lease->start_date->format('Y-m-d')) }}" required />
                </flux:field>
                <flux:field>
                    <flux:label aside="{{ __('Optional') }}">{{ __('End Date') }} </flux:label>
                    <flux:input name="end_date" type="date" value="{{ old('end_date', $lease->end_date?->format('Y-m-d')) }}" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Monthly Rent (KES)') }}</flux:label>
                <flux:input name="monthly_rent" type="number" min="1" step="0.01"
                    value="{{ old('monthly_rent', $lease->monthly_rent) }}" required />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes', $lease->notes) }}</flux:textarea>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('leases.show', $lease) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
