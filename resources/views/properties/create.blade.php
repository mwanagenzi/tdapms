<x-layouts::app :title="__('Add Property')">
    <flux:main class="max-w-2xl space-y-6">

        <div>
            <flux:heading size="xl">{{ __('Add Property') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Create a new property to manage units and tenants.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('properties.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>{{ __('Property Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name') }}" placeholder="e.g. Sunset Apartments" required />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Address') }}</flux:label>
                <flux:textarea name="address" rows="2" placeholder="Street address" required>{{ old('address') }}</flux:textarea>
                @error('address') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input name="city" value="{{ old('city') }}" placeholder="e.g. Nairobi" required />
                    @error('city') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Property Type') }}</flux:label>
                    <flux:select name="type">
                        <flux:option value="apartment" :selected="old('type') === 'apartment'">{{ __('Apartment') }}</flux:option>
                        <flux:option value="commercial" :selected="old('type') === 'commercial'">{{ __('Commercial') }}</flux:option>
                        <flux:option value="mixed" :selected="old('type') === 'mixed'">{{ __('Mixed Use') }}</flux:option>
                    </flux:select>
                    @error('type') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Number of Units') }}</flux:label>
                <flux:input name="number_of_units" type="number" min="1" value="{{ old('number_of_units', 1) }}" required />
                @error('number_of_units') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }} <flux:label.suffix>{{ __('Optional') }}</flux:label.suffix></flux:label>
                <flux:textarea name="description" rows="3">{{ old('description') }}</flux:textarea>
                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Property') }}</flux:button>
                <flux:button href="{{ route('properties.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

    </flux:main>
</x-layouts::app>
