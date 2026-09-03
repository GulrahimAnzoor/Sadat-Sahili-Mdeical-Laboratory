@props(['tone' => 'slate'])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'teal' => 'bg-teal-50 text-teal-800 dark:bg-teal-950 dark:text-teal-200',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
        'rose' => 'bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
        'sky' => 'bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
        'emerald' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-1 text-xs font-medium '.($tones[$tone] ?? $tones['slate'])]) }}>
    {{ $slot }}
</span>
