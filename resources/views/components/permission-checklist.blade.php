@props(['groups', 'selected' => []])

@php
    $checked = collect($selected)->map(fn ($value) => (string) $value)->all();
@endphp

<div class="grid gap-4">
    @foreach ($groups as $group => $permissions)
        <fieldset class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">{{ $group }}</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($permissions as $permission)
                    <label class="flex cursor-pointer items-start gap-2 rounded-xl px-1 py-1 text-sm text-slate-700 hover:bg-white dark:text-slate-200 dark:hover:bg-slate-900">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->value }}"
                            class="mt-0.5 size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600/30"
                            @checked(in_array($permission->value, $checked, true))
                        >
                        <span>{{ $permission->label() }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
</div>
