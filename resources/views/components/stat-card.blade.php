@props([
    'label',
    'value',
    'href' => null,
    'hint' => null,
    'tone' => 'teal',
])

@php
    $iconTones = [
        'teal' => 'bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        'violet' => 'bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-300',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-800']) }}>
@else
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
@endif
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
                @if ($hint)
                    <p class="mt-2 truncate text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
                @endif
            </div>
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl {{ $iconTones[$tone] ?? $iconTones['teal'] }}">
                {{ $slot }}
            </span>
        </div>
@if ($href)
    </a>
@else
    </div>
@endif
