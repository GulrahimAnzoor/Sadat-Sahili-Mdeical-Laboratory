@php
    $sampledAt = isset($visit) ? $visit->created_at : $patient->created_at;
    $referrer = isset($visit) ? $visit->referrerLabel() : $patient->referrerLabel();
@endphp

<section class="report-patient">
    <dl class="report-patient-grid">
        <div>
            <dt>{{ __('Patient') }}</dt>
            <dd>{{ $patient->name }}</dd>
        </div>
        <div>
            <dt>{{ __('Gender') }}</dt>
            <dd>{{ $patient->genderLabel() }}</dd>
        </div>
        <div>
            <dt>{{ __('File no.') }}</dt>
            <dd>{{ $patient->file_number ?: '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Age') }}</dt>
            <dd>{{ $patient->ageLabel() !== '' ? $patient->ageLabel() : '—' }}</dd>
        </div>
        <div>
            <dt>{{ __("Father's name") }}</dt>
            <dd>{{ $patient->father_name ?: '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Phone') }}</dt>
            <dd>{{ $patient->phone ?: '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Ref By') }}</dt>
            <dd>{{ $referrer }}</dd>
        </div>
        <div>
            <dt>{{ __('Sample') }}</dt>
            <dd>{{ $sampledAt?->format('Y-m-d H:i') ?: '—' }}</dd>
        </div>
        <div>
            <dt>{{ __('Reported') }}</dt>
            <dd>{{ now()->format('Y-m-d H:i') }}</dd>
        </div>
        @isset($visit)
            <div>
                <dt>{{ __('Token') }}</dt>
                <dd>{{ $visit->token_code ?: '—' }}</dd>
            </div>
        @endisset
    </dl>
</section>
