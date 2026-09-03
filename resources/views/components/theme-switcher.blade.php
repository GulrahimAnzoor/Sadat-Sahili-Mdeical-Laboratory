@props(['variant' => 'toolbar'])

@php
    $theme = $theme ?? config('lab.default_theme');
    $options = [
        'light' => ['label' => __('Light'), 'icon' => 'sun'],
        'dark' => ['label' => __('Dark'), 'icon' => 'moon'],
        'system' => ['label' => __('System'), 'icon' => 'system'],
    ];
@endphp

<div {{ $attributes->class($variant === 'menu'
    ? 'px-3 pb-3 pt-2'
    : 'inline-flex rounded-2xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-800') }}>
    @if ($variant === 'menu')
        <p class="mb-2 px-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Theme') }}</p>
    @endif
    <div class="{{ $variant === 'menu' ? 'grid grid-cols-3 gap-1 rounded-2xl bg-slate-50 p-1 dark:bg-slate-950/60' : 'flex items-center gap-0.5' }}">
        @foreach ($options as $value => $option)
            <a
                href="{{ route('theme.switch', $value) }}"
                @class([
                    'flex flex-col items-center justify-center gap-1 rounded-xl px-2.5 py-2 text-[11px] font-medium transition',
                    'border border-sky-500 bg-white text-sky-600 shadow-sm dark:border-sky-400 dark:bg-slate-900 dark:text-sky-300' => $theme === $value,
                    'border border-transparent text-slate-500 hover:bg-white hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' => $theme !== $value,
                ])
                aria-current="{{ $theme === $value ? 'true' : 'false' }}"
                title="{{ $option['label'] }}"
            >
                @if ($option['icon'] === 'sun')
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36-1.4-1.4M7.05 7.05 5.64 5.64m12.72 0-1.41 1.41M7.05 16.95l-1.41 1.41M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" /></svg>
                @elseif ($option['icon'] === 'moon')
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 14.3A8.5 8.5 0 1 1 9.7 3 7 7 0 0 0 21 14.3Z" /></svg>
                @else
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.5A2 2 0 0 1 6.5 4.5h11A2 2 0 0 1 19.5 6.5v8a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-8Z" /><path stroke-linecap="round" d="M8 18.5h8M12 16.5v2" /></svg>
                @endif
                <span>{{ $option['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
