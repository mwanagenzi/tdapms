<x-layouts::app :title="__('Edit Tenant')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">Edit Tenant</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $tenant->user->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name', $tenant->user->name) }}" required />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Phone (MPESA)') }}</flux:label>
                <flux:input name="phone" value="{{ old('phone', $tenant->user->phone) }}" required />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('National ID Number') }}</flux:label>
                <flux:input name="id_number" value="{{ old('id_number', $tenant->id_number) }}" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Emergency Contact Name') }}</flux:label>
                    <flux:input name="emergency_contact_name" value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Emergency Contact Phone') }}</flux:label>
                    <flux:input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}" />
                </flux:field>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('tenants.show', $tenant) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
