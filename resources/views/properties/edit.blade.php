<x-layouts::app :title="__('Edit Property')">
    <flux:main class="mx-auto w-1/2 space-y-6">

        <div>
            <flux:heading size="xl">{{ __('Edit Property') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $property->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('properties.update', $property) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Property Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name', $property->name) }}" required />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Address') }}</flux:label>
                <flux:textarea name="address" rows="2" required>{{ old('address', $property->address) }}</flux:textarea>
                @error('address') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input name="city" value="{{ old('city', $property->city) }}" required />
                    @error('city') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Property Type') }}</flux:label>
                    <flux:select name="type">
                        @foreach (['apartment', 'commercial', 'mixed'] as $type)
                        <option value="{{ $type }}"  @selected(old('type', $property->type) === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </flux:select>
                    @error('type') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Number of Units') }}</flux:label>
                <flux:input name="number_of_units" type="number" min="1" value="{{ old('number_of_units', $property->number_of_units) }}" required />
                @error('number_of_units') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea name="description" rows="3">{{ old('description', $property->description) }}</flux:textarea>
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('properties.show', $property) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

    </flux:main>
</x-layouts::app>
