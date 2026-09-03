<x-layout :title="__('Edit assigned test')">
    <form method="POST" action="{{ route('patient-tests.update', $patientTest) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('patient-tests._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Update') }}</x-btn>
            <x-btn :href="route('patient-tests.show', $patientTest)" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
