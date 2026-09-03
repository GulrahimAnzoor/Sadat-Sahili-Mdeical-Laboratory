<x-layout :title="__('New expense')">
    <form method="POST" action="{{ route('expenses.store') }}" class="lab-card max-w-xl space-y-4 p-6">
        @csrf
        @include('expenses._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Save') }}</x-btn><x-btn :href="route('expenses.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
