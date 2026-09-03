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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert([
            ['name' => 'Reception', 'slug' => 'reception', 'description' => 'Front desk and visits', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Laboratory', 'slug' => 'laboratory', 'description' => 'Results and worklist', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Cash ledger and accounts', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Full laboratory settings', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
        Schema::dropIfExists('roles');
    }
};
