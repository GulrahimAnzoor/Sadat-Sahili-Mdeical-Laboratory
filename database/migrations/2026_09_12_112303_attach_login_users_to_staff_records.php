<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $managerId = DB::table('roles')->where('slug', 'manager')->value('id');
        $linkedUserIds = DB::table('staff')->whereNotNull('user_id')->pluck('user_id');

        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            if ($linkedUserIds->contains($user->id)) {
                continue;
            }

            DB::table('staff')->insert([
                'user_id' => $user->id,
                'name' => $user->name,
                'role_id' => $user->is_admin ? $managerId : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
