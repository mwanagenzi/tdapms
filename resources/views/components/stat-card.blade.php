@props([
    'icon' => 'chart-bar',
    'label' => '',
    'value' => '—',
    'sub' => null,
    'color' => 'blue',
])

@php
$colors = [
    'blue'    => 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
    'indigo'  => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400',
    'green'   => 'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400',
    'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
    'amber'   => 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
    'orange'  => 'bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400',
    'sky'     => 'bg-sky-50 text-sky-600 dark:bg-sky-950 dark:text-sky-400',
    'violet'  => 'bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400',
    'rose'    => 'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
];
$iconClass = $colors[$color] ?? $colors['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900']) }}>
    <div class="flex items-center justify-between">
        <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</flux:text>
        <span class="{{ $iconClass }} flex size-9 items-center justify-center rounded-lg">
            <flux:icon :name="$icon" class="size-4" />
        </span>
    </div>
    <div class="mt-2">
        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $value }}</p>
        @if ($sub)
            <flux:text class="mt-0.5 text-xs text-zinc-400">{{ $sub }}</flux:text>
        @endif
    </div>
</div>
