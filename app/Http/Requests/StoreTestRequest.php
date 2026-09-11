<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', Rule::exists('departments', 'slug')],
            'price' => ['required', 'numeric', 'min:0'],
            'normal_range' => ['required', 'string', 'max:20000'],
            'is_active' => ['sometimes', 'boolean'],
            'interpretation' => ['nullable', 'string'],
            'clinical_utility' => ['nullable', 'string'],
            'method' => ['nullable', 'string'],
        ];
    }
}
