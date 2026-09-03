<x-layout :title="__('Doctors')">
    <x-page-header :description="__('Referring doctors and their patients')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('doctors.create')" icon="plus">{{ __('New doctor') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel>
        @if ($doctors->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No doctors have been registered.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Patients') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($doctors as $doctor)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('doctors.show', $doctor) }}" class="font-medium text-teal-800 hover:text-teal-950">{{ $doctor->name }}</a>
                                </td>
                                <td class="px-5 py-3"><x-badge>{{ $doctor->patients_count }}</x-badge></td>
                                <td class="px-5 py-3">
                                    <div class="lab-actions">
                                        <x-btn :href="route('patients.create', ['doctor_id' => $doctor->id])" size="sm" variant="teal" icon="user">{{ __('Patient') }}</x-btn>
                                        <x-btn :href="route('doctors.edit', $doctor)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                        <form method="POST" action="{{ route('doctors.destroy', $doctor) }}">
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
            <div class="border-t border-slate-100 px-5 py-3">{{ $doctors->links() }}</div>
        @endif
    </x-panel>
</x-layout>
