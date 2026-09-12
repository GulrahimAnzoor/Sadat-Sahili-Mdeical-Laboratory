<x-layout :title="__('Patients')">
    <x-page-header :description="__('All laboratory patients, doctors, tests, and results')">
        <x-slot:actions>
            <form method="GET" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search patients') }}" class="lab-input w-52">
                <x-btn type="submit" variant="secondary" icon="search">{{ __('Search') }}</x-btn>
            </form>
            <x-btn :href="route('reception.index')" icon="plus">{{ __('New patient') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        @if ($patients->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No patients have been registered yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Gender') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Doctor') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Visits') }}</th>
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
                                <td class="px-5 py-3">{{ $patient->genderLabel() }}</td>
                                <td class="px-5 py-3">
                                    @if ($patient->doctor)
                                        <a href="{{ route('doctors.show', $patient->doctor) }}" class="text-slate-600 hover:text-teal-800">{{ $patient->doctor->name }}</a>
                                    @else
                                        {{ __('Self request') }}
                                    @endif
                                </td>
                                <td class="px-5 py-3"><x-badge>{{ $patient->visits_count }}</x-badge></td>
                                <td class="px-5 py-3"><x-badge tone="teal">{{ $patient->test_results_count }}</x-badge></td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions">
                                        <x-btn :href="route('reception.visit', $patient)" size="sm" variant="teal" icon="flask">{{ __('Test') }}</x-btn>
                                        <x-btn :href="route('reports.show', $patient)" size="sm" variant="sky" icon="report">{{ __('Report') }}</x-btn>
                                        @can('records.edit')
                                            <x-btn :href="route('patients.edit', $patient)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                        @endcan
                                        @can('records.delete')
                                            <form method="POST" action="{{ route('patients.destroy', $patient) }}">
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
            <div class="border-t border-slate-100 px-5 py-3">{{ $patients->links() }}</div>
        @endif
    </x-panel>
</x-layout>
