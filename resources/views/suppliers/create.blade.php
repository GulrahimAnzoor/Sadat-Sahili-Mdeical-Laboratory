<x-layout :title="__('New supplier')">
    <form method="POST" action="{{ route('suppliers.store') }}" class="lab-card max-w-xl space-y-4 p-6">
        @csrf
        @include('suppliers._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Save') }}</x-btn><x-btn :href="route('suppliers.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
