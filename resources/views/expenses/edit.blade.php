<x-layout :title="__('Edit expense')">
    <form method="POST" action="{{ route('expenses.update', $expense) }}" class="lab-card max-w-xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('expenses._form')
        <div class="flex gap-3"><x-btn type="submit" icon="save">{{ __('Update') }}</x-btn><x-btn :href="route('expenses.show', $expense)" variant="ghost" icon="back">{{ __('Back') }}</x-btn></div>
    </form>
</x-layout>
