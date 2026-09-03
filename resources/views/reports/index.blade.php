<x-layout :title="__('Reports')">
    <x-page-header :description="__('View or print each patient\'s test report')">
        <x-slot:actions>
            <x-btn :href="route('patients.index')" variant="secondary" icon="user">{{ __('Patients') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        @if ($patients->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No reports yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Doctor') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Tests') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Results') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patients as $patient)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $patient) }}" class="font-medium text-teal-800 hover:text-teal-950">{{ $patient->name }}</a>
                                    <p class="text-xs text-slate-500">{{ __('Son/daughter of :name', ['name' => $patient->father_name]) }}</p>
                                </td>
                                <td class="px-5 py-3">{{ $patient->referrerLabel() }}</td>
                                <td class="px-5 py-3"><x-badge>{{ $patient->patient_tests_count }}</x-badge></td>
                                <td class="px-5 py-3"><x-badge tone="teal">{{ $patient->test_results_count }}</x-badge></td>
                                <td class="px-5 py-3">
                                    <x-btn :href="route('reports.show', $patient)" size="sm" variant="teal" icon="report">{{ __('Open report') }}</x-btn>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $patients->links() }}</div>
        @endif
    </x-panel>
</x-layout>
