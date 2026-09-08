<?php

namespace App\Support;

use App\Enums\VisitStatus;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitBilling
{
    public function draftFor(Patient $patient): Visit
    {
        $existing = Visit::query()
            ->with(['patientTests.test'])
            ->whereBelongsTo($patient)
            ->today()
            ->where('status', VisitStatus::Registered)
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $patient->doctor_id,
            'is_self_request' => $patient->doctor_id === null,
            'status' => VisitStatus::Registered,
        ])->load(['patientTests.test']);
    }

    public function attach(Visit $visit, Test $test): Visit
    {
        $this->ensureEditable($visit);

        return DB::transaction(function () use ($visit, $test): Visit {
            if ($visit->patientTests()->where('test_id', $test->id)->doesntExist()) {
                PatientTest::query()->create([
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'test_id' => $test->id,
                    'total_price' => $test->price,
                    'paid' => false,
                    'status' => VisitStatus::Registered,
                ]);
            }

            return $this->recalculate($visit);
        });
    }

    public function detach(Visit $visit, Test $test): Visit
    {
        $this->ensureEditable($visit);

        return DB::transaction(function () use ($visit, $test): Visit {
            $visit->patientTests()->where('test_id', $test->id)->delete();

            return $this->recalculate($visit);
        });
    }

    /**
     * @param  list<int>  $testIds
     */
    public function sync(Visit $visit, array $testIds): Visit
    {
        $this->ensureEditable($visit);

        $testIds = array_values(array_unique(array_map('intval', $testIds)));

        return DB::transaction(function () use ($visit, $testIds): Visit {
            $tests = Test::query()->whereKey($testIds)->get()->keyBy('id');

            if ($testIds === []) {
                $visit->patientTests()->delete();
            } else {
                $visit->patientTests()->whereNotIn('test_id', $testIds)->delete();
            }

            $keptIds = $visit->patientTests()
                ->pluck('test_id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($testIds as $testId) {
                $test = $tests->get($testId);

                if ($test === null || in_array($testId, $keptIds, true)) {
                    continue;
                }

                PatientTest::query()->create([
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'test_id' => $test->id,
                    'total_price' => $test->price,
                    'paid' => false,
                    'status' => VisitStatus::Registered,
                ]);
            }

            return $this->recalculate($visit);
        });
    }

    public function applyDiscount(Visit $visit, float $percent): Visit
    {
        $this->ensureEditable($visit);

        $visit->discount_percent = round($percent, 2);

        return $this->recalculate($visit);
    }

    public function finalize(Visit $visit, bool $paid, CashLedger $ledger): Visit
    {
        if ($visit->patientTests()->doesntExist()) {
            throw ValidationException::withMessages([
                'test_ids' => __('Select at least one test before printing.'),
            ]);
        }

        $visit = $this->recalculate($visit);

        if ($paid) {
            $visit = $this->collectRemaining($visit, $ledger);
        }

        return $visit->fresh(['patientTests.test']);
    }

    public function collectRemaining(Visit $visit, CashLedger $ledger): Visit
    {
        return DB::transaction(function () use ($visit, $ledger): Visit {
            $visit = Visit::query()->lockForUpdate()->findOrFail($visit->id);

            $visit->patientTests()
                ->whereIn('status', [VisitStatus::Registered, VisitStatus::Paid])
                ->update([
                    'paid' => true,
                    'status' => VisitStatus::Paid,
                ]);

            $visit->patientTests()
                ->whereNotIn('status', [VisitStatus::Registered, VisitStatus::Paid])
                ->update(['paid' => true]);

            $visit = $this->recalculate($visit);

            $ledger->recordVisitPayment($visit);

            return $visit->fresh(['patient', 'patientTests.test']);
        });
    }

    public function markTestPaid(PatientTest $patientTest, CashLedger $ledger): Visit
    {
        return DB::transaction(function () use ($patientTest, $ledger): Visit {
            $patientTest = PatientTest::query()->lockForUpdate()->findOrFail($patientTest->id);

            $values = ['paid' => true];

            if (in_array($patientTest->status, [VisitStatus::Registered, VisitStatus::Paid], true)) {
                $values['status'] = VisitStatus::Paid;
            }

            $patientTest->update($values);

            $visit = Visit::query()->lockForUpdate()->findOrFail($patientTest->visit_id);

            $visit = $this->recalculate($visit);
            $ledger->recordVisitPayment($visit);

            return $visit;
        });
    }

    public function recalculate(Visit $visit): Visit
    {
        $tests = $visit->patientTests()->get(['id', 'total_price', 'paid']);
        $subtotal = round((float) $tests->sum('total_price'), 2);
        $percent = round((float) $visit->discount_percent, 2);
        $discountAmount = round($subtotal * $percent / 100, 2);
        $total = round(max(0, $subtotal - $discountAmount), 2);
        $paidSubtotal = round((float) $tests->where('paid', true)->sum('total_price'), 2);
        $allPaid = $tests->isNotEmpty() && $tests->every(fn (PatientTest $row): bool => $row->paid);
        $paidAmount = $allPaid
            ? $total
            : ($subtotal > 0 ? round($total * $paidSubtotal / $subtotal, 2) : 0);

        $status = $visit->status;

        if ($allPaid && in_array($status, [VisitStatus::Registered, VisitStatus::Paid], true)) {
            $status = VisitStatus::Paid;
        }

        $visit->update([
            'subtotal' => $subtotal,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'paid' => $allPaid,
            'status' => $status,
        ]);

        return $visit->fresh(['patientTests.test']);
    }

    /**
     * @return array{visit_id: int, subtotal: float, discount_percent: float, discount_amount: float, total: float, paid_amount: float, remaining: float, test_ids: list<int>}
     */
    public function totals(Visit $visit): array
    {
        return [
            'visit_id' => $visit->id,
            'subtotal' => (float) $visit->subtotal,
            'discount_percent' => (float) $visit->discount_percent,
            'discount_amount' => (float) $visit->discount_amount,
            'total' => (float) $visit->total,
            'paid_amount' => (float) $visit->paid_amount,
            'remaining' => $visit->remainingAmount(),
            'test_ids' => $visit->patientTests->pluck('test_id')->map(fn ($id): int => (int) $id)->all(),
        ];
    }

    private function ensureEditable(Visit $visit): void
    {
        if (in_array($visit->status, [VisitStatus::Paid, VisitStatus::Delivered, VisitStatus::Completed], true)) {
            throw ValidationException::withMessages([
                'visit' => __('This visit can no longer be changed.'),
            ]);
        }
    }
}
