<x-layout :title="__('Reception')">
    <x-page-header :description="__('Register the patient, then choose tests, save the discount, and print tokens.')">
        <x-slot:actions>
            <x-btn :href="route('patients.index')" variant="secondary" icon="user">{{ __('Patients') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('patients.store') }}" class="lab-card mb-8 p-5">
        @csrf
        <p class="mb-4 text-sm font-semibold text-slate-800 dark:text-white">{{ __('New patient') }}</p>
        <div class="grid gap-3 lg:grid-cols-7">
            <div class="lg:col-span-1">
                <label for="name" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="lab-input">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="lg:col-span-1">
                <label for="father_name" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __("Father's name") }}</label>
                <input id="father_name" name="father_name" type="text" value="{{ old('father_name') }}" required class="lab-input">
                @error('father_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gender" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Gender') }}</label>
                <select id="gender" name="gender" required class="lab-input">
                    <option value="">{{ __('Select') }}</option>
                    <option value="male" @selected(old('gender') === 'male')>{{ __('Male') }}</option>
                    <option value="female" @selected(old('gender') === 'female')>{{ __('Female') }}</option>
                </select>
                @error('gender')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <x-age-field compact />
            <div class="lg:col-span-2">
                <label for="doctor_id" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Ref By') }}</label>
                <select id="doctor_id" name="doctor_id" class="lab-input">
                    <option value="">{{ __('Self request') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="phone" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Phone') }}</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="lab-input">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <x-btn type="submit" icon="plus">{{ __('Save and choose tests') }}</x-btn>
        </div>
    </form>

    <x-panel :title="__('Today\'s visits')">
        @if ($visits->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No visits have been registered today.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Queue no.') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Tests') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Total') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Discount') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Paid') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Remaining') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($visits as $visit)
                            <tr>
                                <td class="px-5 py-3 font-bold text-teal-800 dark:text-teal-300">{{ $visit->queue_number ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $visit->patient) }}" class="font-medium">{{ $visit->patient->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $visit->referrerLabel() }}</p>
                                </td>
                                <td class="px-5 py-3 text-xs">{{ $visit->patientTests->pluck('test.name')->filter()->join(', ') }}</td>
                                <td class="px-5 py-3">{{ number_format((float) $visit->total, 2) }}</td>
                                <td class="px-5 py-3">{{ number_format((float) $visit->discount_percent, 0) }}%</td>
                                <td class="px-5 py-3">{{ number_format((float) $visit->paid_amount, 2) }}</td>
                                <td class="px-5 py-3">{{ number_format($visit->remainingAmount(), 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions justify-end">
                                        <x-btn :href="route('visits.token', $visit)" size="sm" variant="teal" icon="ticket">{{ __('Token') }}</x-btn>
                                        <x-btn :href="route('visits.results.edit', $visit)" size="sm" variant="amber" icon="flask">{{ __('Lab') }}</x-btn>
                                        <x-btn :href="route('visits.report', $visit)" size="sm" variant="sky" icon="report">{{ __('Report') }}</x-btn>
                                        @if ($visit->remainingAmount() > 0)
                                            <form method="POST" action="{{ route('visits.payment', $visit) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-btn type="submit" size="sm" variant="amber" icon="pay">{{ __('Pay') }}</x-btn>
                                            </form>
                                        @endif
                                        @if ($visit->whatsappUrl())
                                            <x-btn :href="route('visits.whatsapp', $visit)" size="sm" variant="emerald" icon="chat" target="_blank" rel="noopener">{{ __('WhatsApp') }}</x-btn>
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
