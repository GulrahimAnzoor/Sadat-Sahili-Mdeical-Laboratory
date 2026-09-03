<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'results' => ['required', 'array', 'min:1'],
            'results.*.patient_test_id' => ['required', 'integer', 'exists:patient_tests,id'],
            'results.*.result' => ['nullable', 'string', 'max:255'],
            'results.*.unit' => ['nullable', 'string', 'max:255'],
            'results.*.values' => ['nullable', 'array'],
            'results.*.values.*.test_parameter_id' => ['required', 'integer', 'exists:test_parameters,id'],
            'results.*.values.*.value' => ['nullable', 'string', 'max:255'],
            'results.*.extra_rows' => ['nullable', 'array'],
            'results.*.extra_rows.*' => ['array'],
            'results.*.extra_rows.*.name' => ['nullable', 'string', 'max:255'],
            'results.*.extra_rows.*.value' => ['nullable', 'string', 'max:255'],
            'results.*.extra_rows.*.unit' => ['nullable', 'string', 'max:255'],
            'results.*.extra_rows.*.normal_range' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
