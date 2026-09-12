<?php

namespace App\Models;

use App\Enums\LabPermission;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return 'U';
        }

        $letters = array_slice($parts, 0, 2);

        return implode('', array_map(
            fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)),
            $letters,
        ));
    }

    public function hasPermission(string $ability): bool
    {
        if ($this->is_admin) {
            return true;
        }

        if (! $this->is_active) {
            return false;
        }

        $this->loadMissing('staff.role');

        $permissions = $this->staff?->role?->permissions ?? [];

        return in_array($ability, $permissions, true);
    }

    public function hasLabPermission(LabPermission $permission): bool
    {
        return $this->hasPermission($permission->value);
    }
}
