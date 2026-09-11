<?php

namespace App\Casts;

use App\Enums\TestDepartment;
use App\Models\Department;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<Department, mixed>
 */
class AsDepartment implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Department
    {
        $slug = is_string($value) && $value !== '' ? $value : TestDepartment::Routine->value;

        return Department::resolve($slug);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof TestDepartment) {
            return $value->value;
        }

        if ($value instanceof Department) {
            return $value->slug;
        }

        return is_string($value) && $value !== '' ? $value : TestDepartment::Routine->value;
    }
}
