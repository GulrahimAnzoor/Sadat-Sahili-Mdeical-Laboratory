@php
    $locale = app()->getLocale();
    $currentLabel = config('app.available_locales.'.$locale, $locale);
@endphp

<details {{ $attributes->merge(['class' => 'lab-dropdown relative']) }}>
    <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-sky-200 hover:text-sky-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-sky-800 dark:hover:text-sky-200">
        <svg class="size-4 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M3.5 12h17M12 3c2.4 2.6 3.6 5.7 3.6 9s-1.2 6.4-3.6 9c-2.4-2.6-3.6-5.7-3.6-9s1.2-6.4 3.6-9Z" /></svg>
        <span>{{ $currentLabel }}</span>
        <svg class="lab-dropdown-chevron size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
        <span class="sr-only">{{ __('Language') }}</span>
    </summary>
    <div class="absolute end-0 z-50 mt-2 w-44 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40">
        @foreach (config('app.available_locales') as $code => $label)
            <a
                href="{{ route('locale.switch', $code) }}"
                class="flex items-center justify-between gap-3 px-3 py-2 text-sm transition {{ $locale === $code ? 'bg-sky-50 font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-300' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' }}"
            >
                <span>{{ $label }}</span>
                @if ($locale === $code)
                    <svg class="size-4 text-sky-600 dark:text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10" /></svg>
                @endif
            </a>
        @endforeach
    </div>
</details>
