<x-layouts::app :title="__('Add Caretaker')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Add Caretaker') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Create a caretaker account and assign them to properties.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('caretakers.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name') }}" required />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input name="email" type="email" value="{{ old('email') }}" required />
                    @error('email') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" required />
                    @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label aside="{{ __('Optional') }}">{{ __('ID Number') }} </flux:label>
                <flux:input name="id_number" value="{{ old('id_number') }}" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Password') }}</flux:label>
                    <flux:input name="password" type="password" required />
                    @error('password') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Confirm Password') }}</flux:label>
                    <flux:input name="password_confirmation" type="password" required />
                </flux:field>
            </div>

            @if ($properties->count())
            <flux:field>
                <flux:label aside="{{ __('Optional') }}">{{ __('Assign to Properties') }} </flux:label>
                <div class="mt-1 space-y-2">
                    @foreach ($properties as $property)
                    <label class="flex cursor-pointer items-center gap-2">
                        <flux:checkbox name="property_ids[]" value="{{ $property->id }}"
                            :checked="in_array($property->id, old('property_ids', []))" />
                        <span class="text-sm">{{ $property->name }}</span>
                    </label>
                    @endforeach
                </div>
            </flux:field>
            @endif

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Caretaker') }}</flux:button>
                <flux:button href="{{ route('caretakers.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
