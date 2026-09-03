<x-layout :title="__('Handover')">
    <div class="mb-6 flex justify-end gap-2 print:hidden">
        <x-btn type="button" onclick="window.print()" icon="print">{{ __('Print') }}</x-btn>
        <x-btn :href="route('worklist')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
    </div>

    <article class="print-sheet mx-auto max-w-lg overflow-hidden rounded-3xl border border-teal-200 bg-white p-8 shadow-sm">
        <p class="text-center text-xs tracking-[0.25em] text-teal-700">SSML</p>
        <h2 class="text-center text-lg font-semibold">{{ __('Handover') }}</h2>
        <dl class="mt-6 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Patient') }}</dt><dd class="font-semibold">{{ $patient->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('File no.') }}</dt><dd>{{ $patient->file_number }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Queue no.') }}</dt><dd>{{ $visit->queue_number }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Delivered to') }}</dt><dd>{{ $visit->delivered_to }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Box / fridge') }}</dt><dd>{{ $visit->delivery_box ?: '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Date') }}</dt><dd>{{ $visit->delivered_at?->format('Y-m-d H:i') }}</dd></div>
        </dl>
    </article>
</x-layout>
