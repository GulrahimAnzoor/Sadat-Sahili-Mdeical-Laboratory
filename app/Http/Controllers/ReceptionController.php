<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\AttachVisitTestRequest;
use App\Http\Requests\PrintVisitRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\SyncVisitTestsRequest;
use App\Http\Requests\UpdateVisitBillingRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\Visit;
use App\Support\CashLedger;
use App\Support\VisitBilling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(): View
    {
        return view('reception.index', [
            'doctors' => Doctor::query()->orderBy('name')->get(['id', 'name']),
            'visits' => Visit::query()
                ->with([
                    'patient:id,name',
                    'doctor:id,name',
                    'patientTests:id,visit_id,test_id',
                    'patientTests.test:id,name',
                ])
                ->whereHas('patientTests')
                ->today()
                ->latest('id')
                ->get(),
        ]);
    }

    public function visit(Patient $patient): View
    {
        $patient->load('doctor');

        $visit = Visit::query()
            ->with(['patientTests:id,visit_id,test_id', 'patientTests.test:id,name,price,department'])
            ->whereBelongsTo($patient)
            ->today()
            ->where('status', VisitStatus::Registered)
            ->latest('id')
            ->first();

        $tests = Test::query()->active()->orderBy('department')->orderBy('name')->get(['id', 'name', 'price', 'department']);

        return view('reception.visit', [
            'patient' => $patient,
            'visit' => $visit,
            'doctors' => Doctor::query()->orderBy('name')->get(['id', 'name']),
            'catalogue' => $tests->map(fn (Test $test): array => [
                'id' => $test->id,
                'name' => $test->name,
                'price' => (float) $test->price,
                'department' => $test->department->label(),
            ])->values(),
        ]);
    }

    public function syncTests(SyncVisitTestsRequest $request, Patient $patient, VisitBilling $billing): JsonResponse
    {
        $visit = $billing->sync(
            $billing->draftFor($patient),
            $request->validated('test_ids'),
        );

        return response()->json($billing->totals($visit) + [
            'message' => __('Tests saved.'),
        ]);
    }

    public function attachTest(AttachVisitTestRequest $request, Patient $patient, VisitBilling $billing): JsonResponse
    {
        $test = Test::query()->findOrFail($request->integer('test_id'));
        $visit = $billing->attach($billing->draftFor($patient), $test);

        return response()->json($billing->totals($visit));
    }

    public function detachTest(Patient $patient, Test $test, VisitBilling $billing): JsonResponse
    {
        $visit = Visit::query()
            ->whereBelongsTo($patient)
            ->today()
            ->where('status', VisitStatus::Registered)
            ->latest('id')
            ->firstOrFail();

        return response()->json($billing->totals($billing->detach($visit, $test)));
    }

    public function updateBilling(UpdateVisitBillingRequest $request, Visit $visit, VisitBilling $billing): JsonResponse
    {
        $visit = $billing->applyDiscount($visit, (float) $request->validated('discount_percent'));

        return response()->json($billing->totals($visit) + [
            'message' => __('Discount saved.'),
        ]);
    }

    public function printVisit(PrintVisitRequest $request, Visit $visit, VisitBilling $billing, CashLedger $ledger): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['discount_percent'])) {
            $visit = $billing->applyDiscount($visit, (float) $validated['discount_percent']);
        }

        $visit = $billing->finalize($visit, (bool) $validated['paid'], $ledger);

        return redirect()
            ->route('visits.token', $visit)
            ->with('success', __('Visit saved. Print both token copies.'));
    }

    public function storeVisit(StoreVisitRequest $request, Patient $patient, CashLedger $ledger): RedirectResponse
    {
        $validated = $request->validated();
        $testIds = array_values(array_unique(array_map('intval', $validated['test_ids'])));
        $tests = Test::query()->whereKey($testIds)->get()->keyBy('id');

        $visit = DB::transaction(function () use ($validated, $testIds, $tests, $patient, $ledger): Visit {
            $subtotal = 0.0;

            foreach ($testIds as $testId) {
                $test = $tests->get($testId);

                if ($test === null) {
                    continue;
                }

                $subtotal += (float) $test->price;
            }

            $discountPercent = round((float) ($validated['discount_percent'] ?? 0), 2);
            $discountAmount = round($subtotal * $discountPercent / 100, 2);
            $total = round(max(0, $subtotal - $discountAmount), 2);
            $paid = (bool) $validated['paid'];
            $requestedPaidAmount = round((float) ($validated['paid_amount'] ?? 0), 2);
            $paidAmount = $paid ? $total : min($total, max(0, $requestedPaidAmount));

            if ($paidAmount >= $total && $total > 0) {
                $paid = true;
                $paidAmount = $total;
            }

            $doctorId = $validated['doctor_id'] ?? $patient->doctor_id;
            $isSelfRequest = (bool) ($validated['is_self_request'] ?? false) || $doctorId === null;

            $visit = Visit::query()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $isSelfRequest ? null : $doctorId,
                'is_self_request' => $isSelfRequest,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'paid' => $paid,
                'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
            ]);

            foreach ($testIds as $testId) {
                $test = $tests->get($testId);

                if ($test === null) {
                    continue;
                }

                PatientTest::query()->create([
                    'visit_id' => $visit->id,
                    'patient_id' => $patient->id,
                    'test_id' => $test->id,
                    'total_price' => $test->price,
                    'paid' => $paid,
                    'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
                ]);
            }

            if ($paid) {
                $ledger->recordVisitPayment($visit->fresh());
            }

            return $visit;
        });

        return redirect()
            ->route('visits.token', $visit)
            ->with('success', __('Visit saved. Print both token copies.'));
    }
}
