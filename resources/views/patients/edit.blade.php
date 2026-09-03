<x-layout :title="__('Edit patient: :name', ['name' => $patient->name])">
    <form method="POST" action="{{ route('patients.update', $patient) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('patients._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Update') }}</x-btn>
            <x-btn :href="route('patients.show', $patient)" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
