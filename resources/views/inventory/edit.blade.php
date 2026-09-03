<x-layout :title="__('Edit item')">
    <form method="POST" action="{{ route('inventory-items.update', $item) }}" class="lab-card max-w-xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('inventory._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Update') }}</x-btn><x-btn :href="route('inventory-items.show', $item)" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
