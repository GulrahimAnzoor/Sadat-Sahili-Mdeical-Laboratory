<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RestoreBackupRequest extends FormRequest
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
            'backup' => ['required', 'string', 'max:255', 'regex:/^ssml-backup-[A-Za-z0-9._-]+\.(sql|sqlite)$/'],
            'confirm' => ['accepted'],
        ];
    }
}
