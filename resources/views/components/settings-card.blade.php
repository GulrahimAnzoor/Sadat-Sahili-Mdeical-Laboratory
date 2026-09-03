@props([
    'href',
    'title',
    'description',
    'count' => null,
    'tone' => 'teal',
])

@php
    $tones = [
        'teal' => 'bg-teal-50 text-teal-700 group-hover:bg-teal-700 group-hover:text-white dark:bg-teal-950 dark:text-teal-300',
        'sky' => 'bg-sky-50 text-sky-700 group-hover:bg-sky-700 group-hover:text-white dark:bg-sky-950 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-700 group-hover:bg-amber-700 group-hover:text-white dark:bg-amber-950 dark:text-amber-300',
        'cyan' => 'bg-cyan-50 text-cyan-700 group-hover:bg-cyan-700 group-hover:text-white dark:bg-cyan-950 dark:text-cyan-300',
    ];
    $iconClass = $tones[$tone] ?? $tones['teal'];
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 ease-out hover:-translate-y-0.5 hover:border-teal-400 hover:shadow-lg hover:shadow-teal-700/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-500']) }}
>
    <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-teal-600 via-sky-500 to-cyan-400 opacity-0 transition group-hover:opacity-100"></span>
    <span class="flex items-start justify-between gap-3">
        <span class="inline-flex size-12 items-center justify-center rounded-2xl {{ $iconClass }} transition">
            {{ $icon ?? '' }}
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition group-hover:border-teal-600 group-hover:bg-teal-700 group-hover:text-white dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
            {{ __('Open page') }}
            <svg class="size-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" /></svg>
        </span>
    </span>
    <h3 class="mt-4 text-lg font-semibold text-slate-900 group-hover:text-teal-800 dark:text-white">{{ $title }}</h3>
    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @if ($count !== null)
        <p class="mt-4 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $count }}</p>
    @endif
</a>
