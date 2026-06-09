<x-layouts::app :title="__('Add Tenant')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Add Tenant') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Create a tenant account. They will use this to access the mobile app.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-5">
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
                    <flux:label>{{ __('Phone (MPESA)') }}</flux:label>
                    <flux:input name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX" required />
                    @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label aside="{{ __('Optional') }}">{{ __('National ID Number') }} </flux:label>
                <flux:input name="id_number" value="{{ old('id_number') }}" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label aside="{{ __('Optional') }}">{{ __('Emergency Contact Name') }} </flux:label>
                    <flux:input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" />
                </flux:field>
                <flux:field>
                    <flux:label aside="{{ __('Optional') }}">{{ __('Emergency Contact Phone') }} </flux:label>
                    <flux:input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" />
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

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-400">
                {{ __('The tenant will use their email and password to log in to the mobile app.') }}
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Create Tenant') }}</flux:button>
                <flux:button href="{{ route('tenants.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
