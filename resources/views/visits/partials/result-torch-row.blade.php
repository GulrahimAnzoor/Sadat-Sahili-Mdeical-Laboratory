@php
    $rowName = $extraRow['name'] ?? '';
    $rowValue = $extraRow['value'] ?? '';
    $rowUnit = $extraRow['unit'] ?? '';
    $rowRange = $extraRow['normal_range'] ?? '';
    $rowGroup = $extraRow['group_name'] ?? ($groupName ?? 'IgM');
@endphp
<tr data-extra-row>
    <td class="px-3 py-2 align-top">
        <input name="results[0][extra_rows][{{ $extraIndex }}][name]" value="{{ $rowName }}" class="lab-input" autocomplete="off" placeholder="Rubella">
    </td>
    <td class="px-3 py-2 align-top">
        <input name="results[0][extra_rows][{{ $extraIndex }}][group_name]" value="{{ $rowGroup }}" class="lab-input" autocomplete="off" placeholder="IgM">
    </td>
    <td class="px-3 py-2 align-top">
        <input
            name="results[0][extra_rows][{{ $extraIndex }}][value]"
            value="{{ $rowValue }}"
            data-normal-range="{{ $rowRange }}"
            class="lab-input"
            autocomplete="off"
        >
    </td>
    <td class="px-3 py-2 align-top">
        <input name="results[0][extra_rows][{{ $extraIndex }}][unit]" value="{{ $rowUnit }}" class="lab-input" autocomplete="off" placeholder="Au/ml">
    </td>
    <td class="px-3 py-2 align-top">
        <textarea name="results[0][extra_rows][{{ $extraIndex }}][normal_range]" rows="2" class="lab-input min-h-16">{{ $rowRange }}</textarea>
    </td>
    <td class="px-3 py-2 align-top">
        <button type="button" data-remove-row class="inline-flex items-center justify-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold leading-5 text-rose-700 ring-1 ring-inset ring-rose-200/80 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300 dark:ring-rose-900">{{ __('Remove') }}</button>
    </td>
</tr>
