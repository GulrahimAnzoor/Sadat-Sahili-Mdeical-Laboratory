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
        Schema::table('patients', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone');
            $table->index('created_at');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->index('token_code');
            $table->index(['patient_id', 'created_at']);
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->index(['visit_id', 'status']);
        });

        $this->restrictHistoricalDeletes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->restoreCascades();

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->dropIndex(['visit_id', 'status']);
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['token_code']);
            $table->dropIndex(['patient_id', 'created_at']);
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });
    }

    private function restrictHistoricalDeletes(): void
    {
        if (! $this->supportsMysqlForeignKeys()) {
            return;
        }

        $this->replaceForeignKey('patient_tests', 'patient_id', 'patients', 'restrict');
        $this->replaceForeignKey('patient_tests', 'test_id', 'tests', 'restrict');
        $this->replaceForeignKey('visits', 'patient_id', 'patients', 'restrict');
        $this->replaceForeignKey('test_results', 'patient_id', 'patients', 'restrict');
        $this->replaceForeignKey('test_results', 'test_id', 'tests', 'restrict');
    }

    private function restoreCascades(): void
    {
        if (! $this->supportsMysqlForeignKeys()) {
            return;
        }

        $this->replaceForeignKey('patient_tests', 'patient_id', 'patients', 'cascade');
        $this->replaceForeignKey('patient_tests', 'test_id', 'tests', 'cascade');
        $this->replaceForeignKey('visits', 'patient_id', 'patients', 'cascade');
        $this->replaceForeignKey('test_results', 'patient_id', 'patients', 'cascade');
        $this->replaceForeignKey('test_results', 'test_id', 'tests', 'cascade');
    }

    private function supportsMysqlForeignKeys(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        return in_array($driver, ['mysql', 'mariadb'], true);
    }

    private function replaceForeignKey(string $table, string $column, string $parent, string $onDelete): void
    {
        $constraint = $this->foreignKeyName($table, $column);

        if ($constraint !== null) {
            Schema::table($table, function (Blueprint $blueprint) use ($constraint): void {
                $blueprint->dropForeign($constraint);
            });
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $parent, $onDelete): void {
            $foreign = $blueprint->foreign($column)->references('id')->on($parent);

            if ($onDelete === 'restrict') {
                $foreign->restrictOnDelete();
            } else {
                $foreign->cascadeOnDelete();
            }
        });
    }

    private function foreignKeyName(string $table, string $column): ?string
    {
        $database = Schema::getConnection()->getDatabaseName();

        $name = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        return is_string($name) ? $name : null;
    }
};
