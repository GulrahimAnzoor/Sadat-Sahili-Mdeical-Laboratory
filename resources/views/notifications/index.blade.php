<x-layout :title="__('Notifications')">
    <section class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-sky-800 via-teal-700 to-cyan-600 px-6 py-8 text-white shadow-lg sm:px-8">
        <div class="pointer-events-none absolute -top-16 -end-10 size-48 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-20 -start-8 size-40 rounded-full bg-cyan-300/10"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm text-sky-100">{{ __('Sadat Salihi Medical Laboratory') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('Notifications') }}</h2>
                <p class="mt-2 max-w-2xl text-sm text-sky-50">{{ __('Open a card to view it. The notice is then cleared.') }}</p>
            </div>
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-btn type="submit" variant="glass" size="sm">{{ __('Clear all') }}</x-btn>
                </form>
            @endif
        </div>
    </section>

    @if ($notifications->isEmpty())
        <x-panel>
            <div class="px-6 py-16 text-center">
                <span class="mx-auto mb-4 inline-flex size-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9" /></svg>
                </span>
                <p class="text-base font-semibold text-slate-900 dark:text-white">{{ __('No notifications') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('New patients, low stock, and expiry dates will appear here.') }}</p>
            </div>
        </x-panel>
    @else
        <ul class="grid gap-3">
            @foreach ($notifications as $notification)
                <li>
                    <form method="POST" action="{{ route('notifications.open', $notification) }}">
                        @csrf
                        <button type="submit" class="lab-card flex w-full items-start gap-4 p-4 text-start transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-md dark:hover:border-sky-800">
                            @if (str_starts_with((string) ($notification->data['kind'] ?? ''), 'inventory.expiry.expired'))
                                <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3h.01M12 3.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5Z" /></svg>
                                </span>
                            @elseif (str_contains((string) ($notification->data['kind'] ?? ''), 'expiry') || str_contains((string) ($notification->data['kind'] ?? ''), 'low_stock'))
                                <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3h.01M10.3 4.8 2.7 18.2A1.8 1.8 0 0 0 4.3 21h15.4a1.8 1.8 0 0 0 1.6-2.8L13.7 4.8a1.8 1.8 0 0 0-3.4 0Z" /></svg>
                                </span>
                            @else
                                <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.8 19.5a4.2 4.2 0 0 0-7.6 0M16 8.2a3.2 3.2 0 1 1-6.4 0 3.2 3.2 0 0 1 6.4 0Z" /></svg>
                                </span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $notification->data['title'] ?? __('Notifications') }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                </span>
                                <span class="mt-1 block text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $notification->data['message'] ?? '' }}</span>
                                <span class="mt-2 inline-flex text-xs font-medium text-sky-700 dark:text-sky-300">{{ __('Open and clear') }}</span>
                            </span>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</x-layout>
