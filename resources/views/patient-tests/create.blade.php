<x-layout :title="__('Assign test to patient')">
    <form method="POST" action="{{ route('patient-tests.store') }}" class="lab-card max-w-2xl space-y-4 p-6">
        @csrf
        @include('patient-tests._form')
        <div class="flex gap-3">
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
            <x-btn :href="route('patient-tests.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>
</x-layout>

@push('scripts')
<script>
    const search = document.getElementById('test-search');
    const totalEl = document.getElementById('fee-total');
    const options = document.querySelectorAll('.test-option');

    function updateFee() {
        let sum = 0;
        document.querySelectorAll('input[name="test_ids[]"]:checked').forEach((box) => {
            sum += parseFloat(box.dataset.price || '0');
        });
        if (totalEl) {
            totalEl.textContent = @json(__('Fee')) + ': ' + sum.toFixed(2);
        }
    }

    search?.addEventListener('input', () => {
        const q = search.value.toLowerCase();
        options.forEach((row) => {
            row.classList.toggle('hidden', !row.dataset.name.toLowerCase().includes(q));
        });
    });

    document.querySelectorAll('input[name="test_ids[]"]').forEach((box) => box.addEventListener('change', updateFee));
    updateFee();
</script>
@endpush
