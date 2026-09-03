<?php

namespace App\Http\Requests;

use App\Models\Test;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'paid' => $this->boolean('paid'),
        ]);

        if ($this->filled('test_ids') && is_array($this->input('test_ids'))) {
            return;
        }

        if (! $this->filled('total_price') && $this->filled('test_id')) {
            $price = Test::query()->whereKey($this->integer('test_id'))->value('price');

            if ($price !== null) {
                $this->merge(['total_price' => $price]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'test_id' => ['required_without:test_ids', 'nullable', 'exists:tests,id'],
            'test_ids' => ['required_without:test_id', 'nullable', 'array', 'min:1'],
            'test_ids.*' => ['integer', 'exists:tests,id'],
            'total_price' => ['required_without:test_ids', 'nullable', 'numeric', 'min:0'],
            'paid' => ['required', 'boolean'],
        ];
    }
}
