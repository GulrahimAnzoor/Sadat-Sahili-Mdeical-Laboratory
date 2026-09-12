@php
    $showCasts = $showCasts ?? true;
    $reportTitle = $reportTitle ?? __('Urine Exam Report');
    $groups = [
        \App\Support\LabPanelTemplates::Physical,
        \App\Support\LabPanelTemplates::Chemical,
        \App\Support\LabPanelTemplates::Microscopic,
    ];
    $official = \App\Support\LabPanelTemplates::officialNames((string) ($test?->name ?? ''));
    $castParameter = $parameters->first(fn ($parameter): bool => strcasecmp((string) $parameter->name, 'Casts') === 0);
    $visible = $parameters->filter(function ($parameter) use ($testResult, $castParameter, $official): bool {
        if ($castParameter && (int) $parameter->id === (int) $castParameter->id) {
            return false;
        }

        if ($official !== [] && ! in_array($parameter->name, $official, true) && ! filled($testResult->valueFor($parameter->id))) {
            return false;
        }

        return filled($testResult->valueFor($parameter->id));
    });

    if ($visible->isEmpty()) {
        $visible = $parameters
            ->reject(fn ($parameter): bool => $castParameter && (int) $parameter->id === (int) $castParameter->id)
            ->filter(fn ($parameter): bool => $official === [] || in_array($parameter->name, $official, true));
    }

    $grouped = $visible->groupBy(fn ($parameter) => $parameter->group_name ?: '');
    $castValue = $showCasts && $castParameter
        ? ($testResult->valueFor($castParameter->id) ?: $castParameter->normal_range)
        : null;
@endphp

<div class="report-urine">
    <h3 class="report-urine-title">{{ $reportTitle }}</h3>

    <div class="report-urine-grid">
        @foreach ($groups as $groupName)
            @if ($grouped->get($groupName, collect())->isEmpty())
                @continue
            @endif
            <section class="report-urine-col">
                <h4>{{ $groupName }}</h4>
                <table>
                    <tbody>
                        @foreach ($grouped->get($groupName, collect()) as $parameter)
                            @php
                                $stored = $testResult->valueFor($parameter->id);
                                $abnormal = $testResult->values->firstWhere('test_parameter_id', $parameter->id)?->isAbnormal() ?? false;
                            @endphp
                            <tr>
                                <th>{{ $parameter->name }}</th>
                                <td class="report-result {{ $abnormal ? 'is-abnormal' : '' }}">{{ $stored ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>

    @if (filled($castValue))
        <p class="report-urine-cast">{{ __('Casts') }}: <strong>{{ $castValue }}</strong></p>
    @endif
</div>
