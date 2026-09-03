<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_self_request')->default(false);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->unsignedInteger('queue_number')->nullable();
            $table->string('token_code')->nullable();
            $table->string('status')->default('registered');
            $table->string('delivered_to')->nullable();
            $table->string('delivery_box')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'created_at']);
            $table->index('type');
        });

        Schema::create('test_result_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_parameter_id')->constrained()->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->timestamps();
            $table->unique(['test_result_id', 'test_parameter_id']);
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->nullOnDelete();
        });

        $now = now();

        DB::table('accounts')->insert([
            'name' => 'Reception',
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->backfillVisits();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable(false)->change();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_id');
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_id');
        });

        Schema::dropIfExists('test_result_values');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('visits');
    }

    private function backfillVisits(): void
    {
        $accountId = DB::table('accounts')->where('is_default', true)->value('id');
        $patientTests = DB::table('patient_tests')->orderBy('id')->get();

        if ($patientTests->isEmpty()) {
            return;
        }

        $groups = $patientTests->groupBy(
            fn (object $row): string => $row->patient_id.'|'.substr((string) $row->created_at, 0, 10),
        );

        foreach ($groups as $rows) {
            $first = $rows->first();
            $patient = DB::table('patients')->where('id', $first->patient_id)->first();
            $subtotal = round((float) $rows->sum(fn (object $row): float => (float) $row->total_price), 2);
            $paidAmount = round((float) $rows->where('paid', true)->sum(fn (object $row): float => (float) $row->total_price), 2);
            $paid = $rows->every(fn (object $row): bool => (bool) $row->paid);
            $status = $paid ? 'paid' : 'registered';
            $createdAt = $first->created_at;

            $visitId = DB::table('visits')->insertGetId([
                'patient_id' => $first->patient_id,
                'doctor_id' => $patient?->doctor_id,
                'is_self_request' => $patient?->doctor_id === null,
                'subtotal' => $subtotal,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'total' => $subtotal,
                'paid_amount' => $paidAmount,
                'paid' => $paid,
                'queue_number' => $first->queue_number,
                'token_code' => $first->token_code,
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $first->updated_at,
            ]);

            DB::table('patient_tests')
                ->whereIn('id', $rows->pluck('id'))
                ->update(['visit_id' => $visitId]);

            $tokenCode = $first->token_code ?: 'SSML-'.Carbon::parse($createdAt)->format('Ymd').'-'.str_pad((string) $visitId, 4, '0', STR_PAD_LEFT);

            DB::table('visits')->where('id', $visitId)->update([
                'token_code' => $tokenCode,
                'queue_number' => $first->queue_number ?: $visitId,
            ]);

            if ($paidAmount > 0 && $accountId !== null) {
                DB::table('cash_transactions')->insert([
                    'account_id' => $accountId,
                    'visit_id' => $visitId,
                    'type' => 'in',
                    'amount' => $paidAmount,
                    'description' => 'Visit '.$tokenCode,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
};
