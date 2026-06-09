<x-layouts::app :title="$title ?? __('Coming Soon')">
    <flux:main>
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="wrench-screwdriver" class="size-8 text-zinc-400" />
            </div>
            <flux:heading size="xl" class="mb-2">{{ $title ?? __('Coming Soon') }}</flux:heading>
            <flux:text class="max-w-sm text-zinc-500 dark:text-zinc-400">
                {{ __('This section is being built. Check back after the next phase.') }}
            </flux:text>
        </div>
    </flux:main>
</x-layouts::app>
