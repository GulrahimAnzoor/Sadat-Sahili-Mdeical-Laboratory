<?php

namespace App\Http\Requests;

use App\Enums\Sensitivity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $results = collect($this->input('results', []))->map(function (mixed $row): mixed {
            if (! is_array($row)) {
                return $row;
            }

            if (isset($row['materials']) && is_array($row['materials'])) {
                $row['materials'] = collect($row['materials'])
                    ->filter(fn (mixed $material): bool => is_array($material) && (int) ($material['inventory_item_id'] ?? 0) > 0)
                    ->values()
                    ->all();
            }

            if (isset($row['sensitivities']) && is_array($row['sensitivities'])) {
                $row['sensitivities'] = collect($row['sensitivities'])
                    ->filter(fn (mixed $item): bool => is_array($item)
                        && filled($item['antibiotic'] ?? null)
                        && filled($item['sensitivity'] ?? null))
                    ->values()
                    ->all();
            }

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
            'results.*.extra_rows.*.group_name' => ['nullable', 'string', 'max:255'],
            'results.*.organism' => ['nullable', 'string', 'max:255'],
            'results.*.colony_count' => ['nullable', 'string', 'max:255'],
            'results.*.gram_stain' => ['nullable', 'string', 'max:255'],
            'results.*.specimen' => ['nullable', 'string', 'max:255'],
            'results.*.culture_method' => ['nullable', 'string', 'max:255'],
            'results.*.sensitivities' => ['nullable', 'array'],
            'results.*.sensitivities.*.antibiotic' => ['required', 'string', 'max:255'],
            'results.*.sensitivities.*.sensitivity' => ['required', Rule::enum(Sensitivity::class)],
            'results.*.consume_materials' => ['sometimes', 'boolean'],
            'results.*.materials' => ['nullable', 'array'],
            'results.*.materials.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'results.*.materials.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
