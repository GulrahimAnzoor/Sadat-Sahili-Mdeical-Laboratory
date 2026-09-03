<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $lots = collect($this->input('lots', []))
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['name'] ?? null))
            ->map(function (array $row): array {
                $row['name'] = trim((string) $row['name']);

                return $row;
            })
            ->values()
            ->all();

        $this->merge([
            'category' => trim((string) $this->input('category', '')),
            'lots' => $lots,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'received_on' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'lots' => ['required', 'array', 'min:1'],
            'lots.*.name' => ['required', 'string', 'max:255'],
            'lots.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lots.*.min_quantity' => ['required', 'numeric', 'min:0'],
            'lots.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'lots.*.batch_number' => ['nullable', 'string', 'max:100'],
            'lots.*.expires_on' => ['nullable', 'date'],
        ];
    }
}
