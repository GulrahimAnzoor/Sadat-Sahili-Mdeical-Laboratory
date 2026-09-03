@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => $active
            ? 'group lab-nav-link lab-nav-link-active'
            : 'group lab-nav-link',
    ]) }}
>
    @isset($icon)
        <span @class([
            'lab-nav-icon',
            'lab-nav-icon-active' => $active,
        ])>{{ $icon }}</span>
    @endisset
    <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>
    @if ($active)
        <span class="size-1.5 shrink-0 rounded-full bg-white/90 shadow-[0_0_8px_rgba(255,255,255,0.8)]"></span>
    @endif
</a>
