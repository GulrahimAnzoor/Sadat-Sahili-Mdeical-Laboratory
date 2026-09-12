<x-layout :title="$patient->name">
    <x-page-header :description="__('Father\'s name: :name', ['name' => $patient->father_name])">
        <x-slot:actions>
            <x-btn :href="route('reception.visit', $patient)" icon="plus">{{ __('New visit') }}</x-btn>
            <x-btn :href="route('reports.show', $patient)" variant="secondary" icon="report">{{ __('Report') }}</x-btn>
            @can('records.edit')
                <x-btn :href="route('patients.edit', $patient)" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
            @endcan
            <x-btn :href="route('patients.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <dl class="lab-card mb-6 grid gap-4 p-6 sm:grid-cols-3">
        <div>
            <dt class="text-sm text-slate-500">{{ __('File no.') }}</dt>
            <dd class="mt-1 font-medium">{{ $patient->file_number ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Gender') }} / {{ __('Age') }}</dt>
            <dd class="mt-1 font-medium">{{ $patient->genderAndAgeLabel() }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Phone') }}</dt>
            <dd class="mt-1 font-medium">{{ $patient->phone ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Ref By') }}</dt>
            <dd class="mt-1 font-medium">
                @if ($patient->doctor)
                    <a href="{{ route('doctors.show', $patient->doctor) }}" class="text-teal-800 hover:text-teal-950 dark:text-teal-300">{{ $patient->doctor->name }}</a>
                @else
                    {{ __('Self request') }}
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Address') }}</dt>
            <dd class="mt-1 font-medium">{{ $patient->address ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Notes') }}</dt>
            <dd class="mt-1 font-medium">{{ $patient->description ?: '—' }}</dd>
        </div>
    </dl>

    <x-panel class="mb-6" :title="__('Visits')">
        <x-slot:action>
            <x-btn :href="route('reception.visit', $patient)" size="sm" variant="teal" icon="plus">{{ __('New visit') }}</x-btn>
        </x-slot:action>
        @if ($patient->visits->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No visits have been registered.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Tests') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Total') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Paid') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Remaining') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patient->visits->sortByDesc('id') as $visit)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-medium">{{ $visit->created_at->format('Y-m-d') }}</p>
                                    <p class="text-xs text-slate-500">{{ $visit->token_code }} · {{ $visit->referrerLabel() }}</p>
                                </td>
                                <td class="px-5 py-3 text-xs">{{ $visit->patientTests->pluck('test.name')->filter()->join(', ') }}</td>
                                <td class="px-5 py-3">{{ number_format((float) $visit->total, 2) }}</td>
                                <td class="px-5 py-3">{{ number_format((float) $visit->paid_amount, 2) }}</td>
                                <td class="px-5 py-3">{{ number_format($visit->remainingAmount(), 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions justify-end">
                                        <x-btn :href="route('visits.token', $visit)" size="sm" variant="teal" icon="ticket">{{ __('Token') }}</x-btn>
                                        <x-btn :href="route('visits.results.edit', $visit)" size="sm" variant="amber" icon="flask">{{ __('Lab') }}</x-btn>
                                        <x-btn :href="route('visits.report', $visit)" size="sm" variant="sky" icon="print">{{ __('Print') }}</x-btn>
                                        @if ($visit->remainingAmount() > 0)
                                            <form method="POST" action="{{ route('visits.payment', $visit) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-btn type="submit" size="sm" variant="amber" icon="pay">{{ __('Pay') }}</x-btn>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-panel>
</x-layout>
