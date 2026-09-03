<?php

namespace App\Http\Requests;

use App\Enums\Sensitivity;
use App\Models\PatientTest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rows = collect($this->input('sensitivities', []))
            ->filter(fn ($row): bool => filled($row['antibiotic'] ?? ''))
            ->values()
            ->all();

        $this->merge(['sensitivities' => $rows]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'test_id' => ['required', 'exists:tests,id'],
            'result' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:255'],
            'organism' => ['nullable', 'string', 'max:255'],
            'colony_count' => ['nullable', 'string', 'max:255'],
            'gram_stain' => ['nullable', 'string', 'max:255'],
            'specimen' => ['nullable', 'string', 'max:255'],
            'culture_method' => ['nullable', 'string', 'max:255'],
            'sensitivities' => ['nullable', 'array'],
            'sensitivities.*.antibiotic' => ['required', 'string', 'max:255'],
            'sensitivities.*.sensitivity' => ['required', Rule::enum(Sensitivity::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['patient_id', 'test_id'])) {
                    return;
                }

                $assigned = PatientTest::query()
                    ->where('patient_id', $this->integer('patient_id'))
                    ->where('test_id', $this->integer('test_id'))
                    ->exists();

                if (! $assigned) {
                    $validator->errors()->add('test_id', __('This test has not been assigned to this patient.'));
                }
            },
        ];
    }
}
