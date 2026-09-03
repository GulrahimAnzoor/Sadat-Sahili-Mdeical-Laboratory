<x-layout :title="$doctor->name">
    <x-page-header :description="__('Referring doctor')">
        <x-slot:actions>
            <x-btn :href="route('patients.create', ['doctor_id' => $doctor->id])" icon="plus">{{ __('New patient') }}</x-btn>
            <x-btn :href="route('doctors.edit', $doctor)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            <x-btn :href="route('doctors.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <dl class="lab-card mb-6 grid gap-4 p-6 sm:grid-cols-3">
        <div>
            <dt class="text-sm text-slate-500">{{ __('Specialty') }}</dt>
            <dd class="mt-1 font-medium">{{ $doctor->specialty ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Clinic') }}</dt>
            <dd class="mt-1 font-medium">{{ $doctor->clinic ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Contracted doctor') }}</dt>
            <dd class="mt-1"><x-badge :tone="$doctor->is_contracted ? 'teal' : 'slate'">{{ $doctor->is_contracted ? __('Yes') : __('No') }}</x-badge></dd>
        </div>
    </dl>

    <x-panel :title="__('Patients')">
        @if ($doctor->patients->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('This doctor has no patients.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __("Father's name") }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Gender') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Tests') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Results') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($doctor->patients as $patient)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $patient) }}" class="font-medium text-teal-800 hover:text-teal-950">{{ $patient->name }}</a>
                                </td>
                                <td class="px-5 py-3">{{ $patient->father_name }}</td>
                                <td class="px-5 py-3">{{ $patient->genderLabel() }}</td>
                                <td class="px-5 py-3"><x-badge>{{ $patient->patient_tests_count }}</x-badge></td>
                                <td class="px-5 py-3"><x-badge tone="teal">{{ $patient->test_results_count }}</x-badge></td>
                                <td class="px-5 py-3">
                                    <x-btn :href="route('reports.show', $patient)" size="sm" variant="sky" icon="report">{{ __('Report') }}</x-btn>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-panel>
</x-layout>
