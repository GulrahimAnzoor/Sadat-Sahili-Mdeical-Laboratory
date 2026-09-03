<?php

namespace App\Models;

use App\Enums\LabPermission;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'permissions'])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role): void {
            if (filled($role->slug)) {
                return;
            }

            $role->slug = Str::slug($role->name);
        });
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function allows(LabPermission $permission): bool
    {
        return in_array($permission->value, $this->permissions ?? [], true);
    }
}
