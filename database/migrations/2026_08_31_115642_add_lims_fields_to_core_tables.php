<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('file_number')->nullable()->unique()->after('id');
            $table->unsignedTinyInteger('age')->nullable()->after('gender');
            $table->string('phone')->nullable()->after('age');
            $table->string('address')->nullable()->after('phone');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('name');
            $table->string('clinic')->nullable()->after('specialty');
            $table->boolean('is_contracted')->default(true)->after('clinic');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->string('department')->default('routine')->after('code');
            $table->boolean('is_active')->default(true)->after('normal_range');
            $table->text('interpretation')->nullable();
            $table->text('clinical_utility')->nullable();
            $table->text('method')->nullable();
            $table->index('department');
            $table->index('is_active');
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->string('token_code')->nullable()->after('id');
            $table->unsignedInteger('queue_number')->nullable()->after('token_code');
            $table->string('status')->default('registered')->after('paid');
            $table->index('status');
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->string('organism')->nullable();
            $table->string('colony_count')->nullable();
            $table->string('gram_stain')->nullable();
            $table->string('specimen')->nullable();
            $table->string('culture_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['file_number', 'age', 'phone', 'address']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['specialty', 'clinic', 'is_contracted']);
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'code',
                'department',
                'is_active',
                'interpretation',
                'clinical_utility',
                'method',
            ]);
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['token_code', 'queue_number', 'status']);
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn([
                'organism',
                'colony_count',
                'gram_stain',
                'specimen',
                'culture_method',
            ]);
        });
    }
};
