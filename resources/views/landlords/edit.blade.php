<x-layouts::app :title="__('Edit Landlord')">
    <flux:main class="max-w-xl space-y-6">
        <div>
            <flux:heading size="xl">Edit Landlord</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ $landlord->user->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('landlords.update', $landlord) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:input name="name" value="{{ old('name', $landlord->user->name) }}" required />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input name="phone" value="{{ old('phone', $landlord->user->phone) }}" required />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Company Name') }}</flux:label>
                <flux:input name="company_name" value="{{ old('company_name', $landlord->company_name) }}" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:input name="address" value="{{ old('address', $landlord->address) }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input name="city" value="{{ old('city', $landlord->city) }}" />
                </flux:field>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('landlords.show', $landlord) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::app>
