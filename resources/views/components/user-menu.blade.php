@auth
    @php
        $user = auth()->user();
    @endphp

    <details {{ $attributes->merge(['class' => 'lab-dropdown relative']) }}>
        <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white py-1 ps-1 pe-2.5 shadow-sm transition hover:border-sky-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-sky-800">
            <span class="inline-flex size-8 items-center justify-center rounded-full bg-sky-600 text-[11px] font-semibold tracking-wide text-white">{{ $user->initials() }}</span>
            <span class="hidden max-w-36 truncate text-sm font-medium text-slate-800 sm:inline dark:text-slate-100">{{ $user->name }}</span>
            <svg class="lab-dropdown-chevron size-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
        </summary>
        <div class="absolute end-0 z-50 mt-2 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40">
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <span class="inline-flex size-10 items-center justify-center rounded-full bg-sky-600 text-sm font-semibold text-white">{{ $user->initials() }}</span>
                <div class="min-w-0">
                    <p class="truncate font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                </div>
            </div>

            <x-theme-switcher variant="menu" />

            <div class="border-t border-slate-100 p-2 dark:border-slate-800">
                @can('settings.view')
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                        <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 13.2a1.4 1.4 0 0 0 .3 1.6l.1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.4 1.4 0 0 0-1.6-.3 1.4 1.4 0 0 0-.8 1.3V18a1.7 1.7 0 0 1-3.4 0v-.1a1.4 1.4 0 0 0-.8-1.3 1.4 1.4 0 0 0-1.6.3l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.4 1.4 0 0 0 .3-1.6 1.4 1.4 0 0 0-1.3-.8H6a1.7 1.7 0 0 1 0-3.4h.1a1.4 1.4 0 0 0 1.3-.8 1.4 1.4 0 0 0-.3-1.6l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.4 1.4 0 0 0 1.6.3h.1A1.4 1.4 0 0 0 12 4.2V4a1.7 1.7 0 0 1 3.4 0v.1a1.4 1.4 0 0 0 .8 1.3h.1a1.4 1.4 0 0 0 1.6-.3l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.4 1.4 0 0 0-.3 1.6v.1a1.4 1.4 0 0 0 1.3.8H20a1.7 1.7 0 0 1 0 3.4h-.1a1.4 1.4 0 0 0-1.3.8Z" /></svg>
                        {{ __('Settings') }}
                    </a>
                @endcan
                <a href="{{ route('password.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 11V8.2a4.5 4.5 0 0 1 9 0V11M8 11h8.2A1.8 1.8 0 0 1 18 12.8v5.4A1.8 1.8 0 0 1 16.2 20H8a1.8 1.8 0 0 1-1.8-1.8v-5.4A1.8 1.8 0 0 1 8 11Z" /></svg>
                    {{ __('Password & security') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 12H4m0 0 3.2-3.2M4 12l3.2 3.2M10 5h6.2A2.8 2.8 0 0 1 19 7.8v8.4A2.8 2.8 0 0 1 16.2 19H10" /></svg>
                        {{ __('Log out') }}
                    </button>
                </form>
            </div>
        </div>
    </details>
@endauth
