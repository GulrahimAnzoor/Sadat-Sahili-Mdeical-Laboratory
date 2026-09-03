<x-layout :title="__('New test')">
    <form method="POST" action="{{ route('tests.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @include('tests._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
            <x-btn :href="route('tests.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
