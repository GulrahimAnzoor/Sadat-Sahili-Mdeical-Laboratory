<x-layout :title="__('New result')">
    <form method="POST" action="{{ route('test-results.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        @csrf
        @include('test-results._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
            <x-btn :href="route('test-results.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>

@push('scripts')
<script>
    const testSelect = document.getElementById('test_id');
    const unitInput = document.getElementById('unit');
    testSelect?.addEventListener('change', () => {
        const unit = testSelect.selectedOptions[0]?.dataset.unit;
        if (unit && unitInput && !unitInput.value) {
            unitInput.value = unit;
        }
    });
</script>
@endpush
