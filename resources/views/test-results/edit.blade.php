<x-layout :title="__('Edit result')">
    <form method="POST" action="{{ route('test-results.update', $testResult) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('test-results._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Update') }}</x-btn>
            <x-btn :href="route('test-results.show', $testResult)" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
