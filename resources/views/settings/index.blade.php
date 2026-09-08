<x-layout :title="__('Settings')">
    <section class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-teal-800 via-sky-700 to-cyan-600 px-6 py-8 text-white shadow-lg sm:px-8">
        <div class="pointer-events-none absolute -top-16 -end-10 size-48 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-20 -start-8 size-40 rounded-full bg-cyan-300/10"></div>
        <div class="relative">
            <p class="text-sm text-teal-100">{{ __('Sadat Salihi Medical Laboratory') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('Laboratory settings') }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-teal-50">{{ __('Open a card to manage doctors, staff roles, cash tills, or the test catalogue.') }}</p>
        </div>
    </section>

    <div class="mb-8">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('People') }}</p>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @can('doctors.manage')
                <x-settings-card :href="route('doctors.index')" :title="__('Doctors')" :description="__('Save referring doctors for Ref By.')" :count="$doctorCount" tone="teal">
                    <x-slot:icon>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 4.5v3.2c0 2.4 1.5 4.3 3.5 5.3 2-1 3.5-2.9 3.5-5.3V4.5M8.5 6.2H7.2A2.2 2.2 0 0 0 5 8.4V10c0 2.8 2.4 5 6.5 6.4C15.6 15 18 12.8 18 10V8.4a2.2 2.2 0 0 0-2.2-2.2H15.5" /></svg>
                    </x-slot:icon>
                </x-settings-card>
            @endcan
            @can('staff.manage')
                <x-settings-card :href="route('settings.staff.index')" :title="__('Staff and roles')" :description="__('Create roles and assign each worker a till.')" :count="$staffCount" tone="sky">
                    <x-slot:icon>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0m8-8a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm6 8a3.4 3.4 0 0 0-3-3.3M18.5 8.4a2.4 2.4 0 1 0-2.2-3.3" /></svg>
                    </x-slot:icon>
                </x-settings-card>
            @endcan
            @can('accounts.manage')
                <x-settings-card :href="route('settings.accounts.index')" :title="__('Cash accounts')" :description="__('Named tills for reception, manager, and staff.')" :count="$accountCount" tone="amber">
                    <x-slot:icon>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16v9.2A1.8 1.8 0 0 1 18.2 18.5H5.8A1.8 1.8 0 0 1 4 16.7V7.5Zm0 0L12 12l8-4.5" /></svg>
                    </x-slot:icon>
                </x-settings-card>
            @endcan
        </div>
    </div>

    <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Catalogue') }}</p>
        <div class="grid gap-4 lg:grid-cols-2">
            @can('tests.manage')
                <x-settings-card :href="route('settings.test-reports.edit')" :title="__('Report ranges and summaries')" :description="__('Select a test, then paste its range and summary. Printing shows the result on top, the range on the right, and the summary below.')" :count="$summaryCount" tone="sky">
                    <x-slot:icon>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4.5h10M8.5 4.5v3.2L6 12.8V18a4.5 4.5 0 0 0 12 0v-5.2L15.5 7.7V4.5M9.5 15.5h5" /></svg>
                    </x-slot:icon>
                </x-settings-card>
                <x-settings-card :href="route('tests.index')" :title="__('Tests')" :description="__('Open the catalogue to add, edit, or print tests.')" :count="$testCount" tone="cyan">
                    <x-slot:icon>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-5.2 0v4.5L7 13.2V18a5 5 0 0 0 10 0v-4.8L14.2 7.5V3" /></svg>
                    </x-slot:icon>
                </x-settings-card>
            @endcan
            <x-settings-card :href="route('settings.backups.index')" :title="__('Database backups')" :description="__('Create and restore copies of laboratory records on this computer.')" :count="$backupCount" tone="amber">
                <x-slot:icon>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5 16.5 12M12 3v13.5" /></svg>
                </x-slot:icon>
            </x-settings-card>
            <x-settings-card :href="route('settings.goods.index')" :title="__('Item names')" :description="__('Save names that appear in the inventory Category list.')" :count="$goodsCount" tone="teal">
                <x-slot:icon>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15M4.5 17.25h9" /></svg>
                </x-slot:icon>
            </x-settings-card>
        </div>
    </div>
</x-layout>
