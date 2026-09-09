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

    protected function prepareForValidation(): void
    {
        $results = collect($this->input('results', []))->map(function (mixed $row): mixed {
            if (! is_array($row) || ! isset($row['materials']) || ! is_array($row['materials'])) {
                return $row;
            }

            $row['materials'] = collect($row['materials'])
                ->filter(fn (mixed $material): bool => is_array($material) && (int) ($material['inventory_item_id'] ?? 0) > 0)
                ->values()
                ->all();

            return $row;
        })->all();

        $this->merge(['results' => $results]);
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
            'results.*.consume_materials' => ['sometimes', 'boolean'],
            'results.*.materials' => ['nullable', 'array'],
            'results.*.materials.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'results.*.materials.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
