@php
    $logo = public_path((string) config('lab.logo'));
@endphp

<header class="report-letterhead">
    <div class="report-letterhead-grid" dir="ltr">
        <div class="report-letterhead-en" dir="ltr" lang="en">
            <h1 class="report-brand">
                Saadat Salihi <span class="report-brand-accent">Medical</span>
                <span class="report-brand-line">Laboratory</span>
            </h1>
            <p class="report-badge">{{ config('lab.report_badge') }}</p>
            <p class="report-slogan">{{ config('lab.slogan') }}</p>
        </div>

        <div class="report-letterhead-mark">
            @if (is_file($logo))
                <img src="{{ asset(config('lab.logo')) }}?v=4" alt="{{ config('lab.short_name') }}" class="report-logo" width="128" height="128">
            @else
                <x-brand-mark />
            @endif
        </div>

        <div class="report-letterhead-ps" dir="rtl" lang="ps">
            <h2 class="report-brand-ps">
                سعادت صالحي طبي
                <span class="report-brand-line">لابراتوار</span>
            </h2>
            <p class="report-badge report-badge-ps">{{ config('lab.report_badge_ps') }}</p>
            <p class="report-slogan">{{ config('lab.slogan_ps') }}</p>
        </div>
    </div>
    <span class="report-rule" aria-hidden="true"></span>
</header>
