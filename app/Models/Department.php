<?php

namespace App\Models;

use App\Enums\TestDepartment;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'slug',
    'name',
    'sort_order',
])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    /** @var EloquentCollection<string, self>|null */
    private static ?EloquentCollection $catalogCache = null;

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
    }

    /**
     * @return EloquentCollection<string, self>
     */
    public static function catalog(): EloquentCollection
    {
        return self::$catalogCache ??= static::query()->ordered()->get()->keyBy('slug');
    }

    public static function flushCatalog(): void
    {
        self::$catalogCache = null;
    }

    public static function resolve(string $slug): self
    {
        $department = static::catalog()->get($slug);

        if ($department instanceof self) {
            return $department;
        }

        return new self([
            'slug' => $slug,
            'name' => TestDepartment::tryFrom($slug)?->label() ?? $slug,
        ]);
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'department';
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function label(): string
    {
        return TestDepartment::tryFrom($this->slug)?->label() ?? $this->name;
    }

    /**
     * @return Attribute<string, never>
     */
    protected function value(): Attribute
    {
        return Attribute::get(fn (): string => (string) $this->slug);
    }
}
