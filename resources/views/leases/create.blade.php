<x-layouts::app :title="__('Create Lease')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Create Lease') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ __('Create a lease agreement. A deposit record will be automatically created.') }}
            </flux:text>
        </div>

        <form method="POST" action="{{ route('leases.store') }}" class="space-y-5"
              x-data="{ monthlyRent: {{ old('monthly_rent', 0) }}, depositMonths: {{ $defaultDepositMonths }} }"
              x-init="$watch('monthlyRent', v => $refs.depositAmount.value = (parseFloat(v) * depositMonths).toFixed(2) || 0)">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Tenant') }}</flux:label>
                    <flux:select name="tenant_id" required>
                        <option value="">{{ __('Select tenant…') }}</option>
                        @foreach ($activeTenants as $tenant)
                            <option value="{{ $tenant->id }}"  @selected(old('tenant_id', request('tenant_id')) == $tenant->id)>
                                {{ $tenant->user->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    @error('tenant_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unit') }}</flux:label>
                    <flux:select name="unit_id" required>
                        <option value="">{{ __('Select unit…') }}</option>
                        @foreach ($availableUnits as $unit)
                            <option value="{{ $unit->id }}"  @selected(old('unit_id', request('unit_id')) == $unit->id)>
                                {{ $unit->unit_number }} — {{ $unit->property->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    @error('unit_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Start Date') }}</flux:label>
                    <flux:input name="start_date" type="date" value="{{ old('start_date', date('Y-m-d')) }}" required />
                    @error('start_date') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
                <flux:field>
                    <flux:label aside="{{ __('Leave blank for open-ended') }}">{{ __('End Date') }} </flux:label>
                    <flux:input name="end_date" type="date" value="{{ old('end_date') }}" />
                    @error('end_date') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Monthly Rent (KES)') }}</flux:label>
                    <flux:input name="monthly_rent" type="number" min="1" step="0.01"
                        value="{{ old('monthly_rent') }}"
                        x-model="monthlyRent"
                        required />
                    @error('monthly_rent') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Deposit Amount (KES)') }}</flux:label>
                    <flux:input name="deposit_amount" type="number" min="0" step="0.01"
                        value="{{ old('deposit_amount') }}"
                        x-ref="depositAmount"
                        required />
                    <flux:description>
                        {{ __('Auto-calculated as :months × monthly rent. You can override.', ['months' => $defaultDepositMonths]) }}
                    </flux:description>
                    @error('deposit_amount') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label aside="{{ __('Optional') }}">{{ __('Notes') }} </flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes') }}</flux:textarea>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Lease') }}</flux:button>
                <flux:button href="{{ route('leases.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
