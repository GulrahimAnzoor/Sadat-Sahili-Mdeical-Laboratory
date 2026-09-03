<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'paid' => $this->boolean('paid'),
            'is_self_request' => $this->boolean('is_self_request') || $this->input('doctor_id') === '' || $this->input('doctor_id') === 'self',
        ]);

        if ($this->input('doctor_id') === '' || $this->input('doctor_id') === 'self') {
            $this->merge(['doctor_id' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'test_ids' => ['required', 'array', 'min:1'],
            'test_ids.*' => ['integer', 'exists:tests,id'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'paid' => ['required', 'boolean'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'is_self_request' => ['sometimes', 'boolean'],
        ];
    }
}
