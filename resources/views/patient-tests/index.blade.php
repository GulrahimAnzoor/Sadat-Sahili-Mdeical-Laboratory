<x-layout :title="__('Patient tests')">
    <x-page-header :description="__('Tests assigned to patients, prices, and payment status')">
        <x-slot:actions>
            <x-btn :href="route('patient-tests.create')" icon="plus">{{ __('Assign test') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        @if ($patientTests->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No tests have been assigned.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Test') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Price') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Payment') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patientTests as $patientTest)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $patientTest->patient) }}" class="font-medium text-teal-800 hover:text-teal-950">{{ $patientTest->patient->name }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('tests.show', $patientTest->test) }}" class="text-slate-700 hover:text-teal-800">{{ $patientTest->test->name }}</a>
                                </td>
                                <td class="px-5 py-3">{{ number_format((float) $patientTest->total_price, 2) }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$patientTest->paid ? 'teal' : 'amber'">{{ $patientTest->paid ? __('Paid') : __('Unpaid') }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions">
                                        <x-btn :href="route('patient-tests.show', $patientTest)" size="sm" variant="teal" icon="eye">{{ __('View') }}</x-btn>
                                        <x-btn :href="route('test-results.create', ['patient_id' => $patientTest->patient_id, 'test_id' => $patientTest->test_id])" size="sm" variant="sky" icon="flask">{{ __('Result') }}</x-btn>
                                        @unless ($patientTest->paid)
                                            <form method="POST" action="{{ route('patient-tests.payment', $patientTest) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-btn type="submit" size="sm" variant="amber" icon="pay">{{ __('Pay') }}</x-btn>
                                            </form>
                                        @endunless
                                        <x-btn :href="route('patient-tests.edit', $patientTest)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                        <form method="POST" action="{{ route('patient-tests.destroy', $patientTest) }}">
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
            <div class="border-t border-slate-100 px-5 py-3">{{ $patientTests->links() }}</div>
        @endif
    </x-panel>
</x-layout>
