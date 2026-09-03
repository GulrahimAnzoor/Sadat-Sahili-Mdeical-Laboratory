<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->text('normal_range')->change();
        });

        Schema::table('test_parameters', function (Blueprint $table) {
            $table->text('normal_range')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->string('normal_range')->change();
        });

        Schema::table('test_parameters', function (Blueprint $table) {
            $table->string('normal_range')->nullable()->change();
        });
    }
};
