@props(['unreadCount' => 0])

<a
    href="{{ route('notifications.index') }}"
    {{ $attributes->merge(['class' => 'relative inline-flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-sky-200 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-sky-800 dark:hover:text-sky-200']) }}
>
    <span class="sr-only">{{ __('Notifications') }}</span>
    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9" /></svg>
    @if ((int) $unreadCount > 0)
        <span class="absolute -top-1 -end-1 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-semibold leading-4 text-white">{{ (int) $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
</a>
