@props(['title', 'print' => null])

@php
    $locale = app()->getLocale();
    $isRtl = in_array($locale, ['ps', 'fa'], true);
    $theme = $theme ?? config('lab.default_theme');
    $printClass = $print ? 'print-'.$print : '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-theme="{{ $theme }}" class="{{ $theme === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $theme === 'dark' ? '#020617' : '#f1f5f9' }}">
        <title>{{ $title }} — {{ config('lab.brand') }}</title>
        <x-theme-boot />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|noto-naskh-arabic:400,500,600,700" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        @endif
        @unless ($print)
            <script type="speculationrules">
                {"prefetch":[{"where":{"and":[{"href_matches":"/*"},{"not":{"selector_matches":"[download],[target=_blank],[data-no-prefetch]"}}]},"eagerness":"moderate"}]}
            </script>
        @endunless
    </head>
    <body class="{{ $printClass }} min-h-screen bg-slate-100 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
        <a href="#main-content" class="lab-skip">{{ __('Skip to content') }}</a>

        <input id="lab-nav-toggle" type="checkbox" class="peer sr-only" autocomplete="off">

        <label for="lab-nav-toggle" class="lab-nav-overlay" aria-hidden="true"></label>

        <aside class="lab-sidebar fixed inset-y-0 z-40 flex w-72 flex-col print:hidden">
            <span class="pointer-events-none absolute inset-y-0 start-0 w-1 bg-gradient-to-b from-sky-400 via-sky-500 to-cyan-400" aria-hidden="true"></span>
            <div class="h-1.5 bg-gradient-to-r from-sky-500 via-sky-400 to-cyan-300"></div>
            <div class="relative overflow-hidden border-b border-sky-200/80 bg-sky-50/70 px-4 py-4 dark:border-sky-900/50 dark:bg-sky-950/30">
                <div class="pointer-events-none absolute -top-12 -end-10 size-28 rounded-full bg-sky-300/25 blur-2xl"></div>
                <div class="relative flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3">
                        <x-brand-mark />
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Sadat Salihi') }}</span>
                            <span class="mt-0.5 flex items-center gap-1.5">
                                <span class="truncate text-[11px] text-sky-700/80 dark:text-sky-300/80">{{ __('Medical Laboratory') }}</span>
                                <span class="hidden rounded-md bg-sky-600 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-white sm:inline dark:bg-sky-500">{{ config('lab.short_name') }}</span>
                            </span>
                        </span>
                    </a>
                    <label for="lab-nav-toggle" class="inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-sky-200 bg-white/80 text-sky-700 transition hover:bg-sky-50 lg:hidden dark:border-sky-800 dark:bg-slate-800/80 dark:text-sky-200 dark:hover:bg-sky-950">
                        <span class="sr-only">{{ __('Close menu') }}</span>
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </label>
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto p-3" aria-label="{{ __('Menu') }}">
                @canany(['dashboard.view', 'reception.manage', 'patients.manage'])
                    <p class="lab-nav-heading text-teal-700 dark:text-teal-400">
                        <span class="size-1.5 rounded-full bg-teal-500 shadow-[0_0_8px_rgb(20_184_166/0.7)]"></span>
                        {{ __('Front desk') }}
                    </p>
                    @can('dashboard.view')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 11.2 12 4.2l8.4 7M6.2 10.4V19a1 1 0 0 0 1 1h3.4v-5.2h2.8V20H16.8a1 1 0 0 0 1-1v-8.6" /></svg>
                            </x-slot:icon>
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endcan
                    @can('reception.manage')
                        <x-nav-link :href="route('reception.index')" :active="request()->routeIs('reception.*') || request()->routeIs('visits.token')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7h15M4.5 12h15M4.5 17h8" /></svg>
                            </x-slot:icon>
                            {{ __('Reception') }}
                        </x-nav-link>
                    @endcan
                    @can('patients.manage')
                        <x-nav-link :href="route('patients.index')" :active="request()->routeIs('patients.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.8 19.5a4.2 4.2 0 0 0-7.6 0M16 8.2a3.2 3.2 0 1 1-6.4 0 3.2 3.2 0 0 1 6.4 0ZM19.2 11.2a2.4 2.4 0 1 0-2.3-3.4M20.6 19.5a3.6 3.6 0 0 0-2.8-3.3" /></svg>
                            </x-slot:icon>
                            {{ __('Patients') }}
                        </x-nav-link>
                    @endcan
                @endcanany

                @canany(['lab.manage', 'finance.manage'])
                    <p class="lab-nav-heading text-sky-700 dark:text-sky-400">
                        <span class="size-1.5 rounded-full bg-sky-500 shadow-[0_0_8px_rgb(14_165_233/0.7)]"></span>
                        {{ __('Laboratory') }}
                    </p>
                    @can('lab.manage')
                        <x-nav-link :href="route('worklist')" :active="request()->routeIs('worklist') || request()->routeIs('visits.results.*') || request()->routeIs('visits.report') || request()->routeIs('visits.handover')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12M8 12h8M8 17h5" /><path stroke-linecap="round" stroke-linejoin="round" d="m4.2 7.2 1.2 1.2 2-2M4.2 12.2l1.2 1.2 2-2M4.2 17.2l1.2 1.2 2-2" /></svg>
                            </x-slot:icon>
                            {{ __('Lab') }}
                        </x-nav-link>
                    @endcan
                    @can('finance.manage')
                        <x-nav-link :href="route('finance.index')" :active="request()->routeIs('finance.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m4.2 14V9.5m4.2 9.5V7.2m4.2 11.8v-6.2M20.8 19V4.8" /></svg>
                            </x-slot:icon>
                            {{ __('Finance') }}
                        </x-nav-link>
                    @endcan
                @endcanany

                @canany(['suppliers.manage', 'purchases.manage', 'inventory.manage', 'expenses.manage'])
                    <p class="lab-nav-heading text-amber-700 dark:text-amber-400">
                        <span class="size-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgb(245_158_11/0.7)]"></span>
                        {{ __('Store') }}
                    </p>
                    @can('suppliers.manage')
                        <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20V9.5L12 4l8 5.5V20M9 20v-6h6v6" /></svg>
                            </x-slot:icon>
                            {{ __('Suppliers') }}
                        </x-nav-link>
                    @endcan
                    @can('purchases.manage')
                        <x-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5.5 6.2h14.2l-1.4 8.4H7.2L5.5 6.2 4 4M8.2 20.2a1.1 1.1 0 1 0 0-2.2 1.1 1.1 0 0 0 0 2.2Zm9.2 0a1.1 1.1 0 1 0 0-2.2 1.1 1.1 0 0 0 0 2.2Z" /></svg>
                            </x-slot:icon>
                            {{ __('Purchases') }}
                        </x-nav-link>
                    @endcan
                    @can('inventory.manage')
                        <x-nav-link :href="route('inventory-items.index')" :active="request()->routeIs('inventory-items.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.2 12 3.5 3 8.2l9 4.7 9-4.7Zm0 7.3-9 4.7-9-4.7M3 8.2v7.3m18-7.3v7.3" /></svg>
                            </x-slot:icon>
                            {{ __('Inventory') }}
                        </x-nav-link>
                    @endcan
                    @can('expenses.manage')
                        <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                            <x-slot:icon>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.8 7.5h14.4A1.8 1.8 0 0 1 21 9.3v8.4a1.8 1.8 0 0 1-1.8 1.8H4.8A1.8 1.8 0 0 1 3 17.7V9.3a1.8 1.8 0 0 1 1.8-1.8ZM3 11.2h18M7.5 7.5V5.8A2.3 2.3 0 0 1 9.8 3.5h4.4A2.3 2.3 0 0 1 16.5 5.8V7.5" /></svg>
                            </x-slot:icon>
                            {{ __('Expenses') }}
                        </x-nav-link>
                    @endcan
                @endcanany

                @can('settings.view')
                    <p class="lab-nav-heading text-violet-700 dark:text-violet-400">
                        <span class="size-1.5 rounded-full bg-violet-500 shadow-[0_0_8px_rgb(139_92_246/0.7)]"></span>
                        {{ __('Administration') }}
                    </p>
                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*') || request()->routeIs('doctors.*') || request()->routeIs('tests.*')">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 13.2a1.4 1.4 0 0 0 .3 1.6l.1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.4 1.4 0 0 0-1.6-.3 1.4 1.4 0 0 0-.8 1.3V18a1.7 1.7 0 0 1-3.4 0v-.1a1.4 1.4 0 0 0-.8-1.3 1.4 1.4 0 0 0-1.6.3l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.4 1.4 0 0 0 .3-1.6 1.4 1.4 0 0 0-1.3-.8H6a1.7 1.7 0 0 1 0-3.4h.1a1.4 1.4 0 0 0 1.3-.8 1.4 1.4 0 0 0-.3-1.6l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.4 1.4 0 0 0 1.6.3h.1A1.4 1.4 0 0 0 12 4.2V4a1.7 1.7 0 0 1 3.4 0v.1a1.4 1.4 0 0 0 .8 1.3h.1a1.4 1.4 0 0 0 1.6-.3l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.4 1.4 0 0 0-.3 1.6v.1a1.4 1.4 0 0 0 1.3.8H20a1.7 1.7 0 0 1 0 3.4h-.1a1.4 1.4 0 0 0-1.3.8Z" /></svg>
                        </x-slot:icon>
                        {{ __('Settings') }}
                    </x-nav-link>
                @endcan
            </nav>

            <div class="border-t border-sky-200/70 p-3 dark:border-sky-900/50">
                <div class="rounded-2xl border border-sky-200/70 bg-white/70 p-3.5 dark:border-sky-800/40 dark:bg-slate-900/70">
                    <p class="flex items-start gap-2 text-xs leading-5 text-slate-600 dark:text-slate-300">
                        <svg class="mt-0.5 size-4 shrink-0 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.4S5 14.8 5 9.8A4.4 4.4 0 0 1 12 6.6a4.4 4.4 0 0 1 7 3.2c0 5-7 10.6-7 10.6Z" /></svg>
                        {{ __('Your Health is Our Priority!') }}
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-[11px] text-sky-700/70 dark:text-sky-300/70">
                        <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.2 4.8h2.6l1.2 3.1-1.6 1.1a12 12 0 0 0 5.6 5.6l1.1-1.6 3.1 1.2v2.6A1.8 1.8 0 0 1 16.4 19 14.2 14.2 0 0 1 5 7.6a1.8 1.8 0 0 1 1.2-2.8Z" /></svg>
                        {{ implode(' · ', config('lab.phones')) }}
                    </p>
                </div>
            </div>
        </aside>

        <div class="lab-content">
            <header class="lab-topbar sticky top-0 z-20 border-b border-slate-200/70 bg-white/80 shadow-sm shadow-slate-200/40 backdrop-blur-xl print:hidden dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-slate-950/40">
                <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <label for="lab-nav-toggle" class="inline-flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-teal-200 hover:text-teal-700 lg:hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-teal-700">
                            <span class="sr-only">{{ __('Open menu') }}</span>
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4.5 7h15M4.5 12h15M4.5 17h10" /></svg>
                        </label>
                        <span class="hidden sm:inline-flex lg:hidden">
                            <x-brand-mark size="sm" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-medium tracking-wide text-slate-400">{{ __('Sadat Salihi Medical Laboratory') }}</p>
                            <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 sm:gap-2.5">
                        <x-locale-switcher />
                        @auth
                            <x-notification-menu :unread-count="$unreadNotificationCount ?? 0" />
                            <x-user-menu />
                        @endauth
                    </div>
                </div>
            </header>

            <main id="main-content" class="lab-page px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <x-flash />

        @unless ($print)
            <x-page-transition />
        @endunless

        @stack('scripts')
    </body>
</html>
