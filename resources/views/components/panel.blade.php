@props(['title' => null])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-200 dark:border-slate-800 dark:bg-slate-900']) }}>
    @if ($title || isset($action))
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <div>
                @if ($title)
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                @endif
                @isset($subtitle)
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endisset
            </div>
            @isset($action)
                <div class="shrink-0">{{ $action }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</section>
