<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $abilities = ['records.edit', 'records.delete'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (DB::table('roles')->where('slug', 'manager')->get() as $role) {
            $permissions = json_decode((string) $role->permissions, true) ?: [];
            $permissions = array_values(array_unique(array_merge($permissions, $this->abilities)));

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        foreach (DB::table('roles')->get() as $role) {
            $permissions = json_decode((string) $role->permissions, true) ?: [];
            $permissions = array_values(array_filter(
                $permissions,
                fn (mixed $permission): bool => ! in_array($permission, $this->abilities, true),
            ));

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
                'updated_at' => $now,
            ]);
        }
    }
};
