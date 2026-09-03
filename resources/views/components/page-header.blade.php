@props(['description' => null])

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
    @if ($description)
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @else
        <span></span>
    @endif
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
