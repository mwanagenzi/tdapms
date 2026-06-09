<x-layouts::app :title="__('Add Landlord')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Add Landlord') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Create a landlord account to manage their properties.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('landlords.store') }}" class="space-y-5">
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
                <flux:label aside="{{ __('Optional') }}">{{ __('Company Name') }} </flux:label>
                <flux:input name="company_name" value="{{ old('company_name') }}" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:input name="address" value="{{ old('address') }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input name="city" value="{{ old('city') }}" />
                </flux:field>
            </div>

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

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Landlord') }}</flux:button>
                <flux:button href="{{ route('landlords.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
