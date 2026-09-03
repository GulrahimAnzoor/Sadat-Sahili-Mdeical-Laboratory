<x-layout :title="__('Tests')">
    <x-page-header :description="__('Official laboratory catalogue used for search and visit selection')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('tests.create')" icon="plus">{{ __('New test') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('tests.templates.store') }}" enctype="multipart/form-data" class="lab-card mb-4 flex flex-wrap items-end gap-3 p-4">
        @csrf
        <div class="min-w-56 flex-1">
            <label for="import-file" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Upload Word / PDF') }}</label>
            <input id="import-file" type="file" name="files[]" accept=".pdf,.doc,.docx,application/pdf" multiple required class="lab-input">
            <p class="mt-1 text-xs text-slate-500">{{ __('Name the file like the test (CBC.docx). Ranges print from that file; you only type the result.') }}</p>
            @error('files')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            @error('files.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <x-btn type="submit">{{ __('Upload and link') }}</x-btn>
    </form>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search tests') }}" class="lab-input max-w-xs">
        <select name="department" class="lab-input max-w-xs">
            <option value="">{{ __('All departments') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->value }}" @selected(request('department') === $department->value)>{{ $department->label() }}</option>
            @endforeach
        </select>
        <x-btn type="submit" variant="secondary" icon="search">{{ __('Search') }}</x-btn>
    </form>

    <x-panel>
        @if ($tests->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No tests have been registered.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Department') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Price') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Normal range') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($tests as $test)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('tests.show', $test) }}" class="font-medium text-teal-800 hover:text-teal-950 dark:text-teal-300">{{ $test->name }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $test->department->label() }}</td>
                                <td class="px-5 py-3">{{ number_format((float) $test->price, 2) }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $test->normal_range }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$test->is_active ? 'teal' : 'slate'">{{ $test->is_active ? __('Active') : __('Inactive') }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions">
                                        <x-btn :href="route('patient-tests.create', ['test_id' => $test->id])" size="sm" variant="teal" icon="flask">{{ __('Assign') }}</x-btn>
                                        <x-btn :href="route('tests.edit', $test)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                        <form method="POST" action="{{ route('tests.destroy', $test) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 dark:border-slate-800">{{ $tests->links() }}</div>
        @endif
    </x-panel>
</x-layout>
