<x-layouts::app :title="__('Edit Caretaker')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">Edit Caretaker</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $caretaker->user->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('caretakers.update', $caretaker) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name', $caretaker->user->name) }}" required />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input name="phone" value="{{ old('phone', $caretaker->user->phone) }}" required />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('ID Number') }}</flux:label>
                <flux:input name="id_number" value="{{ old('id_number', $caretaker->id_number) }}" />
            </flux:field>

            @if ($properties->count())
            <flux:field>
                <flux:label>{{ __('Assigned Properties') }}</flux:label>
                <div class="mt-1 space-y-2">
                    @foreach ($properties as $property)
                    <label class="flex cursor-pointer items-center gap-2">
                        <flux:checkbox name="property_ids[]" value="{{ $property->id }}"
                            :checked="in_array($property->id, old('property_ids', $caretaker->properties->pluck('id')->toArray()))" />
                        <span class="text-sm">{{ $property->name }}</span>
                    </label>
                    @endforeach
                </div>
            </flux:field>
            @endif

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('caretakers.show', $caretaker) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
