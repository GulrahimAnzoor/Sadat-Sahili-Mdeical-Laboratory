<x-layout :title="__('Edit supplier')">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="lab-card max-w-xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('suppliers._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Update') }}</x-btn><x-btn :href="route('suppliers.show', $supplier)" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
