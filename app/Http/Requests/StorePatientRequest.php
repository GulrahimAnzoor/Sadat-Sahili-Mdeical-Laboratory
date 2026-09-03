<?php

namespace App\Http\Requests;

use App\Enums\AgeUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('doctor_id') === '' || $this->input('doctor_id') === 'self') {
            $this->merge(['doctor_id' => null]);
        }

        if (! $this->filled('age_unit')) {
            $this->merge(['age_unit' => AgeUnit::Years->value]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $unit = AgeUnit::tryFrom((string) $this->input('age_unit')) ?? AgeUnit::Years;

        return [
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'age' => ['nullable', 'integer', 'min:'.$unit->min(), 'max:'.$unit->max()],
            'age_unit' => ['required', Rule::enum(AgeUnit::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
        ];
    }
}
