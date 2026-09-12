<x-layout :title="__('Results')">
    <x-page-header :description="__('Recorded test results and normal ranges')">
        <x-slot:actions>
            <x-btn :href="route('test-results.create')" icon="plus">{{ __('New result') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        @if ($testResults->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No results have been recorded.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Test') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Result') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Unit') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Range') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($testResults as $testResult)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $testResult->patient) }}" class="font-medium text-teal-800 hover:text-teal-950">{{ $testResult->patient->name }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('tests.show', $testResult->test) }}" class="text-slate-700 hover:text-teal-800">{{ $testResult->test->name }}</a>
                                </td>
                                <td class="px-5 py-3 font-semibold">{{ $testResult->result }}</td>
                                <td class="px-5 py-3">{{ $testResult->unit }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $testResult->test->normal_range }}</td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions">
                                        <x-btn :href="route('test-results.show', $testResult)" size="sm" variant="teal" icon="eye">{{ __('View') }}</x-btn>
                                        <x-btn :href="route('reports.show', $testResult->patient)" size="sm" variant="sky" icon="report">{{ __('Report') }}</x-btn>
                                        @can('records.edit')
                                            <x-btn :href="route('test-results.edit', $testResult)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                        @endcan
                                        @can('records.delete')
                                            <form method="POST" action="{{ route('test-results.destroy', $testResult) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $testResults->links() }}</div>
        @endif
    </x-panel>
</x-layout>
