@props([
    'id' => 'password',
    'name' => 'password',
    'autocomplete' => 'current-password',
    'placeholder' => '••••••••',
])

<div class="relative">
    <span class="pointer-events-none absolute inset-y-0 start-0 flex w-11 items-center justify-center text-slate-400">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 1 0-9 0v3M6.75 10.5h10.5A1.75 1.75 0 0 1 19 12.25v6A1.75 1.75 0 0 1 17.25 20H6.75A1.75 1.75 0 0 1 5 18.25v-6a1.75 1.75 0 0 1 1.75-1.75Z" /></svg>
    </span>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="password"
        required
        autocomplete="{{ $autocomplete }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'auth-input pe-12']) }}
    >
    <button
        type="button"
        data-password-toggle="{{ $id }}"
        class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
        aria-label="{{ __('Show password') }}"
        aria-pressed="false"
    >
        <svg data-password-icon="show" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5c4.64 0 8.58 3.01 9.96 7.18.07.21.07.43 0 .64C20.58 16.49 16.64 19.5 12 19.5c-4.64 0-8.58-3.01-9.96-7.18ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
        <svg data-password-icon="hide" class="hidden size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.5 10.5 0 0 0 12 4.5c4.64 0 8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64 10.6 10.6 0 0 1-1.72 2.73M9.9 4.24A9 9 0 0 0 4.2 7.8M2 2l20 20M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88" /></svg>
    </button>
</div>
