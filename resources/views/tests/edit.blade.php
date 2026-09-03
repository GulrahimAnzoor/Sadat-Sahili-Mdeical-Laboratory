<x-layout :title="__('Edit test: :name', ['name' => $test->name])">
    <form method="POST" action="{{ route('tests.update', $test) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('tests._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Update') }}</x-btn>
            <x-btn :href="route('tests.show', $test)" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>
