<x-layouts::app :title="__('Add Unit')">
    <flux:main class="max-w-xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Add Unit') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Add a rentable unit to a property.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('units.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>{{ __('Property') }}</flux:label>
                <flux:select name="property_id" required>
                    <flux:option value="">{{ __('Select property…') }}</flux:option>
                    @foreach ($properties as $property)
                        <flux:option value="{{ $property->id }}" :selected="old('property_id', request('property_id')) == $property->id">{{ $property->name }}</flux:option>
                    @endforeach
                </flux:select>
                @error('property_id') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Unit Number') }}</flux:label>
                    <flux:input name="unit_number" value="{{ old('unit_number') }}" placeholder="e.g. A1, 201" required />
                    @error('unit_number') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unit Type') }}</flux:label>
                    <flux:select name="type">
                        @foreach (['bedsitter' => 'Bedsitter', '1br' => '1 Bedroom', '2br' => '2 Bedroom', '3br' => '3 Bedroom', 'studio' => 'Studio', 'commercial' => 'Commercial', 'other' => 'Other'] as $val => $label)
                            <flux:option value="{{ $val }}" :selected="old('type') === $val">{{ $label }}</flux:option>
                        @endforeach
                    </flux:select>
                    @error('type') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Floor') }} <flux:label.suffix>{{ __('Optional') }}</flux:label.suffix></flux:label>
                    <flux:input name="floor" value="{{ old('floor') }}" placeholder="e.g. 2nd" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Size (sq ft)') }} <flux:label.suffix>{{ __('Optional') }}</flux:label.suffix></flux:label>
                    <flux:input name="size_sqft" type="number" min="0" value="{{ old('size_sqft') }}" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Notes') }} <flux:label.suffix>{{ __('Optional') }}</flux:label.suffix></flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes') }}</flux:textarea>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Unit') }}</flux:button>
                <flux:button href="{{ route('units.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
