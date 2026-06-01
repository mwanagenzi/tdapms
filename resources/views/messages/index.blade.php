<x-layouts::app :title="__('Messages')">
    <flux:main class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Messages') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Conversations with tenants, caretakers, and landlords.') }}</flux:text>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @forelse ($conversations as $conversation)
            <a href="{{ route('messages.show', $conversation) }}" wire:navigate
               class="flex items-start gap-4 border-b border-zinc-100 px-5 py-4 last:border-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50 transition">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <flux:icon name="message-circle" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium truncate">
                            {{ $conversation->subject ?? class_basename($conversation->context_type) . ' #' . $conversation->context_id }}
                        </p>
                        @if ($conversation->unread_count > 0)
                        <span class="shrink-0 flex size-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                            {{ $conversation->unread_count }}
                        </span>
                        @endif
                    </div>
                    @if ($conversation->latestMessage)
                    <p class="mt-0.5 text-sm text-zinc-500 truncate">
                        <span class="font-medium">{{ $conversation->latestMessage->sender->name }}:</span>
                        {{ Str::limit($conversation->latestMessage->body, 80) }}
                    </p>
                    <p class="mt-0.5 text-xs text-zinc-400">{{ $conversation->latestMessage->created_at->diffForHumans() }}</p>
                    @endif
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <flux:icon name="message-circle" class="mb-3 size-10 text-zinc-300" />
                <flux:heading>{{ __('No conversations yet') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Conversations are created from leases and maintenance requests.') }}</flux:text>
            </div>
            @endforelse
        </div>
        <div>{{ $conversations->links() }}</div>
    </flux:main>
</x-layouts::app>
