<x-layouts::app :title="$inspection->type_label . ' Report'">
    <flux:main class="space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ $inspection->type_label }} Inspection</flux:heading>
                    <flux:badge :color="$inspection->type === 'move_in' ? 'green' : 'orange'">
                        {{ $inspection->type_label }}
                    </flux:badge>
                </div>
                <flux:text class="mt-1 text-zinc-500">
                    {{ $inspection->lease->tenant->user->name }} ·
                    {{ $inspection->lease->unit->unit_number }} ({{ $inspection->lease->unit->property->name }}) ·
                    Conducted by {{ $inspection->conductedBy->name }}
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge :color="$inspection->status === 'completed' ? 'green' : 'amber'" size="lg">
                    {{ ucfirst($inspection->status) }}
                </flux:badge>
                @can('complete inspections')
                @if (! $inspection->isCompleted())
                <form method="POST" action="{{ route('inspections.complete', $inspection) }}">
                    @csrf
                    <flux:button type="submit" variant="primary" size="sm" icon="check">
                        {{ __('Mark Complete') }}
                    </flux:button>
                </form>
                @endif
                @endcan
                @can('update inspections')
                @if (! $inspection->isCompleted())
                <flux:button href="{{ route('inspections.edit', $inspection) }}" size="sm" variant="ghost" wire:navigate>Edit</flux:button>
                @endif
                @endcan
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif
        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($inspection->notes)
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $inspection->notes }}</flux:text>
        </div>
        @endif

        {{-- Stats --}}
        <div class="grid gap-3 sm:grid-cols-4">
            <x-stat-card icon="list-bullet" label="Total Items" value="{{ $inspection->items->count() }}" color="blue" />
            <x-stat-card icon="check-circle" label="Good" value="{{ $inspection->items->where('condition', 'good')->count() }}" color="green" />
            <x-stat-card icon="exclamation-triangle" label="Fair/Damaged" value="{{ $inspection->items->whereIn('condition', ['fair', 'damaged'])->count() }}" color="amber" />
            <x-stat-card icon="x-circle" label="Missing" value="{{ $inspection->items->where('condition', 'missing')->count() }}" color="zinc" />
        </div>

        {{-- Items by category --}}
        @php $byCategory = $inspection->items->groupBy('category'); @endphp
        @foreach ($byCategory as $category => $items)
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
                <flux:heading size="sm">{{ $category }}</flux:heading>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($items as $item)
                <div class="px-5 py-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-sm">{{ $item->item_name }}</p>
                            @if ($item->notes)
                            <p class="mt-0.5 text-xs text-zinc-500">{{ $item->notes }}</p>
                            @endif
                        </div>
                        <flux:badge :color="$item->condition_badge['color']" size="sm">
                            {{ $item->condition_badge['label'] }}
                        </flux:badge>
                    </div>
                    @if ($item->photos->count())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($item->photos as $photo)
                        <a href="{{ $photo->url }}" target="_blank">
                            <img src="{{ $photo->url }}" class="h-16 w-16 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700" alt="{{ $photo->caption ?? $item->item_name }}" />
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </flux:main>
</x-layouts::app>
