<x-layout :title="__('New purchase')">
    <form method="POST" action="{{ route('purchases.store') }}" class="lab-card max-w-6xl space-y-4 p-6">
        @csrf
        @include('purchases._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Save') }}</x-btn><x-btn :href="route('purchases.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
