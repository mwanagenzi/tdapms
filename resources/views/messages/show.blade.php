<x-layouts::app :title="__('Conversation')">
    <flux:main class="flex h-full flex-col space-y-0">

        {{-- Header --}}
        <div class="mb-4 flex items-center gap-3">
            <flux:button href="{{ route('messages.index') }}" variant="ghost" size="sm" icon="arrow-left" wire:navigate>
                {{ __('Messages') }}
            </flux:button>
            <flux:heading size="lg">{{ $conversation->subject ?? __('Conversation') }}</flux:heading>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">{{ session('success') }}</flux:callout>
        @endif

        {{-- Message thread --}}
        <div class="flex-1 space-y-4 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900" style="max-height: 60vh">
            @forelse ($conversation->messages as $message)
            @php $isMe = $message->sender_id === auth()->id(); @endphp
            <div class="flex gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                <flux:avatar :name="$message->sender->name" size="sm" />
                <div class="max-w-lg {{ $isMe ? 'items-end' : '' }} flex flex-col gap-1">
                    <div class="{{ $isMe ? 'bg-blue-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800' }} rounded-2xl {{ $isMe ? 'rounded-tr-sm' : 'rounded-tl-sm' }} px-4 py-2.5">
                        <p class="text-sm {{ $isMe ? 'text-white' : 'text-zinc-900 dark:text-zinc-100' }}">{{ $message->body }}</p>
                    </div>
                    <div class="flex items-center gap-2 px-1 text-xs text-zinc-400">
                        <span>{{ $message->sender->name }}</span>
                        <span>·</span>
                        <span>{{ $message->created_at->diffForHumans() }}</span>
                        @if ($isMe && $message->read_at)
                        <span>· Read</span>
                        @endif
                    </div>
                    @if ($message->attachments->count())
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach ($message->attachments as $attachment)
                        <a href="{{ $attachment->url }}" target="_blank"
                           class="flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon name="paperclip" class="size-3" />
                            {{ $attachment->filename }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-center text-sm text-zinc-400">{{ __('No messages yet. Start the conversation below.') }}</p>
            @endforelse
        </div>

        {{-- Reply form --}}
        @can('send messages')
        <div class="mt-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="POST" action="{{ route('messages.store', $conversation) }}" enctype="multipart/form-data"
                  class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <flux:textarea name="body" rows="2" placeholder="{{ __('Type your message…') }}" required></flux:textarea>
                </div>
                <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Send') }}</flux:button>
            </form>
        </div>
        @endcan

    </flux:main>
</x-layouts::app>
