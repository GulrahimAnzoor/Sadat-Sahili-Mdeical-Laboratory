@props(['href' => null, 'variant' => 'primary', 'type' => 'button', 'size' => 'md', 'icon' => null])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 font-semibold whitespace-nowrap transition duration-150';
    $base .= ' focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600/40 focus-visible:ring-offset-1';
    $base .= ' disabled:pointer-events-none disabled:opacity-45 active:scale-[0.98]';

    $sizes = [
        'sm' => 'rounded-full px-2.5 py-1 text-xs leading-5',
        'md' => 'rounded-xl px-4 py-2 text-sm',
    ];

    $variants = [
        'primary' => 'bg-gradient-to-r from-teal-700 to-sky-700 text-white shadow-sm shadow-teal-700/25 hover:from-teal-800 hover:to-sky-800 hover:shadow-md',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-teal-800 dark:hover:bg-slate-800',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
        'danger' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200/80 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300 dark:ring-rose-900',
        'teal' => 'bg-teal-50 text-teal-800 ring-1 ring-inset ring-teal-200/80 hover:bg-teal-100 dark:bg-teal-950/50 dark:text-teal-200 dark:ring-teal-800',
        'sky' => 'bg-sky-50 text-sky-800 ring-1 ring-inset ring-sky-200/80 hover:bg-sky-100 dark:bg-sky-950/50 dark:text-sky-200 dark:ring-sky-800',
        'amber' => 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200/80 hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-200 dark:ring-amber-800',
        'emerald' => 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200/80 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-200 dark:ring-emerald-800',
        'slate' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200/80 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
        'white' => 'bg-white text-teal-800 shadow-sm hover:bg-teal-50',
        'glass' => 'border border-white/30 bg-white/10 text-white shadow-none hover:bg-white/20',
    ];

    $iconPaths = [
        'plus' => 'M12 5v14m-7-7h14',
        'edit' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897z',
        'trash' => 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.1 48.1 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.1 48.1 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.96 51.96 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.67 48.67 0 0 0-7.5 0',
        'eye' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'print' => 'M6.72 13.829c-.24.03-.48.062-.72.082m.72-.082a42.4 42.4 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.082m-.72-.082L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V6.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v.852',
        'back' => 'M15.75 19.5 8.25 12l7.5-7.5',
        'search' => 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z',
        'flask' => 'M9 4.5h6m-4.5 0v5.25L6 18.75h12L13.5 9.75V4.5',
        'report' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'pay' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
        'ticket' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
        'user' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
        'cart' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
        'chat' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H15.75M8.25 8.25h7.5M21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
        'save' => 'm4.5 12.75 6 6 9-13.5',
        'mail' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
        'check' => 'm4.5 12.75 6 6 9-13.5',
        'list' => 'M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5',
    ];

    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
    $iconClass = $size === 'sm' ? 'size-3.5 shrink-0' : 'size-4 shrink-0';
    $iconPath = is_string($icon) ? ($iconPaths[$icon] ?? null) : null;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconPath)
            <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconPath)
            <svg class="{{ $iconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
        @endif
        {{ $slot }}
    </button>
@endif
