<x-layout :title="__('Token — :name', ['name' => $patient->name])" :print="$size">
    @php
        $tokenBackUrl = $visit ? route('reception.index') : route('patients.show', $patient);
        $lines = $visit?->patientTests ?? $patient->patientTests;
        $subtotal = (float) ($visit?->subtotal ?? $lines->sum('total_price'));
        $discount = (float) ($visit?->discount_amount ?? 0);
        $total = (float) ($visit?->total ?? $subtotal);
        $paid = (float) ($visit?->paid_amount ?? $lines->where('paid', true)->sum('total_price'));
        $queue = $visit?->queue_number ?: $lines->sortByDesc('id')->first()?->queue_number;
        $token = $visit?->token_code ?: $lines->sortByDesc('id')->first()?->token_code;
        $copies = [
            ['key' => 'lab', 'label' => __('Laboratory copy')],
            ['key' => 'patient', 'label' => __('Patient copy')],
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
        @if ($visit)
            <div class="lab-actions">
                <x-btn :href="route('visits.token', ['visit' => $visit, 'size' => 'a5'])" size="sm" :variant="$size === 'a5' ? 'primary' : 'slate'">A5</x-btn>
                <x-btn :href="route('visits.token', ['visit' => $visit, 'size' => 'a6'])" size="sm" :variant="$size === 'a6' ? 'primary' : 'slate'">A6</x-btn>
            </div>
        @endif
        <div class="lab-actions">
            <x-btn type="button" onclick="window.print()" icon="print">{{ __('Print') }}</x-btn>
            <x-btn :href="$tokenBackUrl" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </div>

    <div class="space-y-8">
        @foreach ($copies as $copy)
            <article class="token-copy print-sheet mx-auto max-w-md overflow-hidden rounded-3xl border border-teal-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                <div class="bg-gradient-to-r from-teal-700 to-sky-700 px-6 py-5 text-center text-white">
                    <p class="text-xs tracking-[0.25em]">SSML</p>
                    <h2 class="text-lg font-semibold">{{ __('Sadat Salihi Medical Laboratory') }}</h2>
                    <p class="text-xs text-teal-100">{{ $copy['label'] }}</p>
                    <p class="mt-1 text-xs">{{ implode(' · ', config('lab.phones')) }}</p>
                </div>
                <div class="px-6 py-6 text-center">
                    <p class="text-xs text-slate-500">{{ __('Queue no.') }}</p>
                    <p class="text-6xl font-bold tracking-tight text-teal-800 dark:text-teal-300">{{ $queue ?: '—' }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $token }} · {{ $patient->file_number }}</p>
                </div>
                <dl class="space-y-2 border-t border-slate-100 px-6 py-4 text-sm dark:border-slate-800">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Patient') }}</dt><dd class="font-semibold">{{ $patient->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __("Father's name") }}</dt><dd>{{ $patient->father_name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Gender') }} / {{ __('Age') }}</dt><dd>{{ $patient->genderAndAgeLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Ref By') }}</dt><dd>{{ $visit?->referrerLabel() ?? $patient->referrerLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Date') }}</dt><dd>{{ ($visit?->created_at ?? now())->format('Y-m-d H:i') }}</dd></div>
                </dl>
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Selected tests') }}</p>
                    @forelse ($lines as $patientTest)
                        <div class="flex justify-between text-sm">
                            <span>{{ $patientTest->test->name }}</span>
                            <span>{{ number_format((float) $patientTest->total_price, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No tests have been assigned.') }}</p>
                    @endforelse
                    <div class="mt-3 flex justify-between border-t border-dashed border-slate-200 pt-3 text-sm dark:border-slate-700">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if ($discount > 0)
                        <div class="flex justify-between text-sm">
                            <span>{{ __('Discount') }} {{ number_format((float) ($visit?->discount_percent ?? 0), 0) }}%</span>
                            <span>{{ number_format($discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ __('Net') }}</span>
                        <span>{{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>{{ __('Paid') }}</span>
                        <span>{{ number_format($paid, 2) }}</span>
                    </div>
                </div>
                <p class="bg-slate-50 px-6 py-3 text-center text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ __('Please take this token to the laboratory window.') }}</p>
            </article>
        @endforeach
    </div>
</x-layout>
