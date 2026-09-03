@php
    $builder = config('lab.builder');
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-[1.75rem] border border-white/70 bg-white/35 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/40 lg:grid lg:grid-cols-2']) }}>
    <aside class="relative overflow-hidden bg-gradient-to-br from-indigo-500 via-blue-600 to-violet-800 px-8 py-10 text-white">
        <div class="pointer-events-none absolute -top-16 -end-10 size-48 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute bottom-8 -start-10 size-40 rounded-full bg-fuchsia-400/20"></div>
        <div class="pointer-events-none absolute top-1/3 start-1/2 size-24 rounded-full bg-sky-300/15"></div>

        <div class="relative flex h-full min-h-[28rem] flex-col">
            <div class="flex items-center gap-3">
                <x-anzoor-mark />
                <div>
                    <p class="text-lg font-bold tracking-tight">{{ $builder['brand'] }}</p>
                    <p class="text-xs text-white/75">{{ $builder['name'] }}</p>
                </div>
            </div>

            <h2 class="mt-8 text-3xl font-semibold leading-tight tracking-tight">{{ __('Full-stack products that help businesses grow') }}</h2>
            <p class="mt-3 max-w-md text-sm leading-6 text-white/80">{{ __('I build websites, dashboards, and mobile apps with React, Next.js, PHP, Laravel, and React Native.') }}</p>

            <ul class="mt-8 grid gap-3 text-sm">
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                    </span>
                    {{ __('PHP & Laravel laboratory systems') }}
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                    </span>
                    {{ __('React, Next.js and admin dashboards') }}
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                    </span>
                    {{ __('React Native apps and clean UI/UX') }}
                </li>
            </ul>

            <div class="mt-auto grid gap-3 pt-10 text-sm text-white/85">
                <p>{{ $builder['role'] }} · {{ __('Kabul, Afghanistan') }} · {{ __('2+ years') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $builder['portfolio'] }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium hover:bg-white/25">{{ __('Portfolio') }}</a>
                    <a href="{{ $builder['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium hover:bg-white/25">{{ __('WhatsApp') }} {{ $builder['phone'] }}</a>
                    <a href="mailto:{{ $builder['email'] }}" class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium hover:bg-white/25">{{ $builder['email'] }}</a>
                </div>
            </div>
        </div>
    </aside>

    <section class="bg-white/90 px-6 py-8 sm:px-10 sm:py-10 dark:bg-slate-900/90">
        {{ $slot }}
    </section>
</div>
