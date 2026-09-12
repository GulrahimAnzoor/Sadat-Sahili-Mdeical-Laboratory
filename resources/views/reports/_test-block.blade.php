@php
    $test = $patientTest->test ?? $testResult?->test;
    $parameters = $test?->parameters ?? collect();
    $notes = collect([
        ['title' => __('Clinical utility'), 'body' => $test?->clinical_utility],
        ['title' => __('Method'), 'body' => $test?->method],
    ])->filter(fn (array $note): bool => filled($note['body']));
    $title = collect([$test?->department?->label(), $test?->name])
        ->filter(fn (?string $part): bool => filled($part))
        ->unique()
        ->implode(' ');
@endphp

<section class="report-test-block">
@if ($test && ! $test->hidesStandardTitle())
    <h3 class="report-test-title">{{ $title }}</h3>
@endif

@if ($testResult)
    @if ($test?->report_layout === \App\Enums\ReportLayout::UrineExam)
        @include('reports.partials.urine-exam', ['showCasts' => true])
    @elseif ($test?->report_layout === \App\Enums\ReportLayout::StoolExam)
        @include('reports.partials.urine-exam', [
            'reportTitle' => __('Stool Exam Report'),
            'showCasts' => false,
        ])
    @elseif ($test?->report_layout === \App\Enums\ReportLayout::TorchPanel)
        @include('reports.partials.torch-panel')
    @else
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('Test') }}</th>
                <th>{{ __('Result') }}</th>
                <th>{{ __('Units') }}</th>
                <th>{{ __('Normal range') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($parameters->isNotEmpty())
                @foreach ($parameters->groupBy(fn ($parameter) => $parameter->group_name ?: '') as $group => $params)
                    @if ($group !== '')
                        <tr class="report-group">
                            <td colspan="4">{{ $group }}</td>
                        </tr>
                    @endif
                    @foreach ($params as $parameter)
                        @php
                            $stored = $testResult->valueFor($parameter->id);
                            $display = $stored ?? ($loop->parent->first && $loop->first ? $testResult->result : '—');
                            $abnormal = $testResult->values->firstWhere('test_parameter_id', $parameter->id)?->isAbnormal() ?? false;
                        @endphp
                        <tr>
                            <td>{{ $parameter->name }}</td>
                            <td class="report-result {{ $abnormal ? 'is-abnormal' : '' }}">{{ $display }}</td>
                            <td>{{ $parameter->unit }}</td>
                            <td class="report-range">{{ $parameter->normal_range }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td>{{ $test->name }}</td>
                    <td class="report-result {{ $testResult->isAbnormal() ? 'is-abnormal' : '' }}">{{ $testResult->result }}</td>
                    <td>{{ $testResult->unit }}</td>
                    <td class="report-range">{{ $test->normal_range }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    @endif

    @if ($testResult->relationLoaded('sensitivities') && $testResult->sensitivities->isNotEmpty())
        <table class="report-table report-culture">
            <thead>
                <tr>
                    <th>{{ __('Antibiotic') }}</th>
                    <th>{{ __('Sensitivity') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($testResult->sensitivities as $sensitivity)
                    <tr>
                        <td>{{ $sensitivity->antibiotic }}</td>
                        <td>{{ $sensitivity->sensitivity?->label() ?? $sensitivity->sensitivity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (filled($test?->interpretation))
        <section class="report-summary">
            <h4>{{ __('Summary') }}:</h4>
            <p>{{ $test->interpretation }}</p>
        </section>
    @endif

    @foreach ($notes as $note)
        <section class="report-notes">
            <h4>{{ $note['title'] }}</h4>
            <p>{{ $note['body'] }}</p>
        </section>
    @endforeach
@else
    <p class="report-empty">{{ __('No result has been recorded yet.') }}</p>
    
@endif
</section>
