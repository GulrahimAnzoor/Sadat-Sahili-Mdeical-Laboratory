<?php

namespace App\Http\Requests;

use App\Models\Test;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestReportContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'normal_range' => trim((string) $this->input('normal_range', '')),
            'interpretation' => trim((string) $this->input('interpretation', '')) ?: null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $test = $this->route('test');
        $testId = $test instanceof Test ? $test->id : 0;

        return [
            'normal_range' => ['required', 'string', 'max:20000'],
            'interpretation' => ['nullable', 'string', 'max:20000'],
            'parameters' => ['nullable', 'array'],
            'parameters.*.id' => [
                'required',
                'integer',
                Rule::exists('test_parameters', 'id')->where('test_id', $testId),
            ],
            'parameters.*.normal_range' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
