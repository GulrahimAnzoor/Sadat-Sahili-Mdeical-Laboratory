<x-layout :title="__('Database backups')">
    <x-page-header :description="__('Save a copy of laboratory records on this computer. Backups are never deleted automatically.')">
        <x-slot:actions>
            <x-settings-back />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-panel :title="__('Create backup')">
            <form method="POST" action="{{ route('settings.backups.store') }}" class="space-y-4 p-5">
                @csrf
                <p class="text-sm leading-6 text-slate-500">{{ __('Keep copies on this PC and on a USB drive. Restore only when you need to replace the current database.') }}</p>
                <x-btn type="submit" icon="plus">{{ __('Backup database') }}</x-btn>
            </form>
        </x-panel>

        <x-panel class="lg:col-span-2" :title="__('Saved backups')">
            @if ($backups === [])
                <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No backups yet.') }}</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($backups as $backup)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $backup['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $backup['modified_at']->format('Y-m-d H:i:s') }} · {{ number_format($backup['size'] / 1024, 1) }} KB</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-btn :href="route('settings.backups.download', $backup['name'])" size="sm" variant="secondary">{{ __('Download') }}</x-btn>
                                <form method="POST" action="{{ route('settings.backups.restore') }}" class="flex flex-wrap items-center gap-2" onsubmit="return confirm(@json(__('Restore this backup? Current laboratory data will be replaced.')))">
                                    @csrf
                                    <input type="hidden" name="backup" value="{{ $backup['name'] }}">
                                    <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                                        <input type="checkbox" name="confirm" value="1" required class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                        {{ __('Replace current data') }}
                                    </label>
                                    <x-btn type="submit" size="sm" variant="danger">{{ __('Restore') }}</x-btn>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>
    </div>
</x-layout>
