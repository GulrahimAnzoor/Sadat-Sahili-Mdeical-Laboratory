<x-layout :title="__('New doctor')">
    <form method="POST" action="{{ route('doctors.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @include('doctors._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
            <x-btn :href="route('doctors.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
