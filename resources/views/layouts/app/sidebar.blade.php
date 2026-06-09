<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                {{-- Dashboard --}}
                <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                {{-- Super Admin: Landlord Management --}}
                @role('super_admin')
                <flux:sidebar.group heading="{{ __('System') }}" class="grid">
                    @if (Route::has('landlords.index'))
                        <flux:sidebar.item icon="building-office-2" :href="route('landlords.index')" :current="request()->routeIs('landlords.*')" wire:navigate>
                            {{ __('Landlords') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Landlord: Properties & Setup --}}
                @role('landlord|super_admin')
                <flux:sidebar.group heading="{{ __('Properties') }}" class="grid">
                    @if (Route::has('properties.index'))
                        <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                            {{ __('Properties') }}
                        </flux:sidebar.item>
                    @endif
                    @if (Route::has('units.index'))
                        <flux:sidebar.item icon="home" :href="route('units.index')" :current="request()->routeIs('units.*')" wire:navigate>
                            {{ __('Units') }}
                        </flux:sidebar.item>
                    @endif
                    @if (Route::has('caretakers.index'))
                        <flux:sidebar.item icon="wrench-screwdriver" :href="route('caretakers.index')" :current="request()->routeIs('caretakers.*')" wire:navigate>
                            {{ __('Caretakers') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Caretaker: Assigned Properties --}}
                @role('caretaker')
                <flux:sidebar.group heading="{{ __('Properties') }}" class="grid">
                    @if (Route::has('properties.index'))
                        <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                            {{ __('My Properties') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Tenants & Leases --}}
                @role('super_admin|landlord|caretaker')
                <flux:sidebar.group heading="{{ __('Tenants') }}" class="grid">
                    @if (Route::has('tenants.index'))
                        <flux:sidebar.item icon="user-group" :href="route('tenants.index')" :current="request()->routeIs('tenants.*')" wire:navigate>
                            {{ __('Tenants') }}
                        </flux:sidebar.item>
                    @endif
                    @if (Route::has('leases.index'))
                        <flux:sidebar.item icon="document-text" :href="route('leases.index')" :current="request()->routeIs('leases.*')" wire:navigate>
                            {{ __('Leases') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Finances: Deposits --}}
                @role('super_admin|landlord|caretaker')
                <flux:sidebar.group heading="{{ __('Finances') }}" class="grid">
                    @if (Route::has('deposits.index'))
                        <flux:sidebar.item icon="shield-check" :href="route('deposits.index')" :current="request()->routeIs('deposits.*')" wire:navigate>
                            {{ __('Deposits & Escrow') }}
                        </flux:sidebar.item>
                    @endif
                    @if (Route::has('deposit-deductions.index'))
                        <flux:sidebar.item icon="minus-circle" :href="route('deposit-deductions.index')" :current="request()->routeIs('deposit-deductions.*')" wire:navigate>
                            {{ __('Deductions') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Operations --}}
                @role('super_admin|landlord|caretaker')
                <flux:sidebar.group heading="{{ __('Operations') }}" class="grid">
                    @if (Route::has('inspections.index'))
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('inspections.index')" :current="request()->routeIs('inspections.*')" wire:navigate>
                            {{ __('Inspections') }}
                        </flux:sidebar.item>
                    @endif
                    @if (Route::has('maintenance.index'))
                        <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance.index')" :current="request()->routeIs('maintenance.*')" wire:navigate>
                            {{ __('Maintenance') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Communication --}}
                @role('super_admin|landlord|caretaker')
                <flux:sidebar.group heading="{{ __('Communication') }}" class="grid">
                    @if (Route::has('messages.index'))
                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                            {{ __('Messages') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole

                {{-- Reports (Landlord only) --}}
                @role('super_admin|landlord')
                <flux:sidebar.group heading="{{ __('Analytics') }}" class="grid">
                    @if (Route::has('reports.index'))
                        <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endrole
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <div class="flex flex-col flex-1 min-w-0">

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        </div>{{-- flex-col flex-1 content wrapper --}}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
