@props(['size' => 'md'])

@php
    $markClass = $size === 'sm' ? 'size-9' : 'size-11';
    $logo = public_path((string) config('lab.logo'));
@endphp

@if (is_file($logo))
    <img
        src="{{ asset(config('lab.logo')) }}?v=4"
        alt="{{ config('lab.short_name') }}"
        {{ $attributes->merge(['class' => $markClass.' rounded-full bg-white object-contain shadow-sm']) }}
    >
@else
    @php
        $fallbackClass = $size === 'sm' ? 'size-9 rounded-xl' : 'size-11 rounded-2xl';
        $iconClass = $size === 'sm' ? 'size-5' : 'size-6';
    @endphp
    <span {{ $attributes->merge(['class' => 'lab-brand-mark relative inline-flex '.$fallbackClass.' items-center justify-center overflow-hidden text-white shadow-lg shadow-teal-700/30']) }}>
        <svg class="{{ $iconClass }} relative z-10" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="1.7" d="M9 3h6" />
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.2 3v4.6L6.6 14.8A5.2 5.2 0 0 0 11.1 22h1.8a5.2 5.2 0 0 0 4.5-7.2L13.8 7.6V3" />
            <path fill="currentColor" fill-opacity="0.35" d="M8.15 15.35h7.7c.28.95.38 2.02.16 3.05-.48 2.15-2.38 3.1-4.16 3.1h-1.4c-1.78 0-3.68-.95-4.16-3.1a7.3 7.3 0 0 1 .16-3.05Z" />
            <circle cx="13.15" cy="17.35" r="0.7" fill="currentColor" fill-opacity="0.85" />
            <circle cx="10.9" cy="18.55" r="0.45" fill="currentColor" fill-opacity="0.55" />
        </svg>
        <span class="lab-brand-shine" aria-hidden="true"></span>
    </span>
@endif
