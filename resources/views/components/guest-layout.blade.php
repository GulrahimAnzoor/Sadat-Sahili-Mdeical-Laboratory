@props(['title'])

@php
    $locale = app()->getLocale();
    $isRtl = in_array($locale, ['ps', 'fa'], true);
    $theme = $theme ?? config('lab.default_theme');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-theme="{{ $theme }}" class="{{ $theme === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $theme === 'dark' ? '#020617' : '#eef2ff' }}">
        <title>{{ $title }} — {{ config('lab.brand') }}</title>
        <x-theme-boot />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="auth-shell min-h-screen font-sans text-slate-800 antialiased dark:text-slate-100">
        <div class="relative min-h-screen overflow-hidden">
            <span class="auth-blob -start-10 top-10 size-40 bg-rose-300/80"></span>
            <span class="auth-blob start-1/3 top-6 size-16 bg-lime-300/90"></span>
            <span class="auth-blob -end-8 top-24 size-44 bg-orange-300/80"></span>
            <span class="auth-blob bottom-10 start-8 size-36 bg-violet-300/70"></span>
            <span class="auth-blob -bottom-8 end-1/4 size-48 bg-fuchsia-300/60"></span>
            <span class="auth-blob start-1/2 top-1/2 size-28 bg-sky-300/50"></span>

            <div class="relative flex min-h-screen flex-col">
                <header class="flex items-center justify-end gap-2 px-4 py-4 sm:px-8">
                    <x-locale-switcher />
                    <x-theme-switcher />
                </header>

                <main class="lab-page flex flex-1 items-center justify-center px-4 pb-10 sm:px-8">
                    <div class="w-full max-w-6xl">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <x-flash />
        <x-page-transition />
        <script>
            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-password-toggle]');
                if (!toggle) {
                    return;
                }

                event.preventDefault();

                const input = document.getElementById(toggle.getAttribute('data-password-toggle'));
                if (!input) {
                    return;
                }

                const showPlain = input.type === 'password';
                input.type = showPlain ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', showPlain ? 'true' : 'false');
                toggle.setAttribute('aria-label', showPlain ? @json(__('Hide password')) : @json(__('Show password')));
                toggle.querySelector('[data-password-icon="show"]')?.classList.toggle('hidden', showPlain);
                toggle.querySelector('[data-password-icon="hide"]')?.classList.toggle('hidden', !showPlain);
            });
        </script>
        @stack('scripts')
    </body>
</html>
