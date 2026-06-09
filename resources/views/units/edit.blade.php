<x-layouts::app :title="__('Edit Unit')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">Edit Unit {{ $unit->unit_number }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $unit->property->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('units.update', $unit) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Property') }}</flux:label>
                <flux:select name="property_id" required>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}"  @selected(old('property_id', $unit->property_id) == $property->id)>{{ $property->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Unit Number') }}</flux:label>
                    <flux:input name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" required />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Unit Type') }}</flux:label>
                    <flux:select name="type">
                        @foreach (['bedsitter' => 'Bedsitter', '1br' => '1 Bedroom', '2br' => '2 Bedroom', '3br' => '3 Bedroom', 'studio' => 'Studio', 'commercial' => 'Commercial', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}"  @selected(old('type', $unit->type) === $val)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Floor') }}</flux:label>
                    <flux:input name="floor" value="{{ old('floor', $unit->floor) }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Size (sq ft)') }}</flux:label>
                    <flux:input name="size_sqft" type="number" value="{{ old('size_sqft', $unit->size_sqft) }}" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select name="status">
                    @foreach (['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance'] as $val => $label)
                        <option value="{{ $val }}"  @selected(old('status', $unit->status) === $val)>{{ $label }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes', $unit->notes) }}</flux:textarea>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('units.show', $unit) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
