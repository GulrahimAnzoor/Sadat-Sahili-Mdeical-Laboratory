<x-layout :title="__('Edit purchase')">
    <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="lab-card max-w-6xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('purchases._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Update') }}</x-btn><x-btn :href="route('purchases.show', $purchase)" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
