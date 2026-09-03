<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_active')->default(true)->after('is_admin');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('description');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });

        $now = now();

        $rolePermissions = [
            'reception' => [
                'dashboard.view',
                'reception.manage',
                'patients.manage',
            ],
            'laboratory' => [
                'dashboard.view',
                'lab.manage',
                'reports.view',
            ],
            'finance' => [
                'dashboard.view',
                'finance.manage',
                'accounts.manage',
            ],
            'manager' => [
                'dashboard.view',
                'reception.manage',
                'patients.manage',
                'doctors.manage',
                'tests.manage',
                'lab.manage',
                'reports.view',
                'finance.manage',
                'suppliers.manage',
                'purchases.manage',
                'inventory.manage',
                'expenses.manage',
                'settings.view',
                'staff.manage',
                'accounts.manage',
            ],
        ];

        foreach ($rolePermissions as $slug => $permissions) {
            DB::table('roles')->where('slug', $slug)->update([
                'permissions' => json_encode(array_values($permissions)),
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_active']);
        });
    }
};
