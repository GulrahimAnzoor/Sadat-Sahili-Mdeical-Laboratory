@php
    $rows = $parameters->filter(fn ($parameter): bool => filled($testResult->valueFor($parameter->id)));

    if ($rows->isEmpty()) {
        $rows = $parameters;
    }

    $rowCount = max($rows->count(), 1);
@endphp

<table class="report-table report-torch">
    <thead>
        <tr>
            <th>{{ __('Test') }}</th>
            <th></th>
            <th></th>
            <th>{{ __('Result') }}</th>
            <th>{{ __('Normal range') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $parameter)
            @php
                $stored = $testResult->valueFor($parameter->id);
                $display = $stored ?: ($loop->first ? ($testResult->result ?: '—') : '—');
                $abnormal = $testResult->values->firstWhere('test_parameter_id', $parameter->id)?->isAbnormal() ?? false;
                $antibody = $parameter->group_name ?: 'IgM';
                $resultCell = trim($display.($parameter->unit ? ' '.$parameter->unit : ''));
            @endphp
            <tr>
                @if ($loop->first)
                    <th class="report-torch-label" rowspan="{{ $rowCount }}">
                        {{ __('TORCH Profile') }}
                    </th>
                @endif
                <td>{{ $parameter->name }}</td>
                <td>{{ $antibody }}</td>
                <td class="report-result {{ $abnormal ? 'is-abnormal' : '' }}">{{ $resultCell }}</td>
                <td class="report-range">{{ $parameter->normal_range }}</td>
            </tr>
        @empty
            <tr>
                <th class="report-torch-label">{{ __('TORCH Profile') }}</th>
                <td colspan="4">—</td>
            </tr>
        @endforelse
    </tbody>
</table>
