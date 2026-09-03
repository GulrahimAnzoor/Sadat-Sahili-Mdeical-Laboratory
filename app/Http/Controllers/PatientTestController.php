<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\StorePatientTestRequest;
use App\Http\Requests\UpdatePatientTestRequest;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Visit;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientTestController extends Controller
{
    public function index(): View
    {
        return view('patient-tests.index', [
            'patientTests' => PatientTest::query()
                ->with(['patient', 'test', 'visit'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('patient-tests.create', [
            'patients' => Patient::query()->orderBy('name')->get(),
            'tests' => Test::query()->active()->orderBy('department')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePatientTestRequest $request, CashLedger $ledger): RedirectResponse
    {
        $validated = $request->validated();
        $testIds = $validated['test_ids'] ?? [$validated['test_id']];
        $testIds = array_values(array_unique(array_map('intval', $testIds)));

        $tests = Test::query()->whereKey($testIds)->get()->keyBy('id');
        $patient = Patient::query()->findOrFail($validated['patient_id']);

        $created = DB::transaction(function () use ($validated, $testIds, $tests, $patient, $ledger) {
            $subtotal = 0.0;

            foreach ($testIds as $testId) {
                $test = $tests->get($testId);

                if ($test === null) {
                    continue;
                }

                $subtotal += count($testIds) === 1 && isset($validated['total_price'])
                    ? (float) $validated['total_price']
                    : (float) $test->price;
            }

            $paid = (bool) $validated['paid'];

            $visit = Visit::query()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $patient->doctor_id,
                'is_self_request' => $patient->doctor_id === null,
                'subtotal' => $subtotal,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'total' => $subtotal,
                'paid_amount' => $paid ? $subtotal : 0,
                'paid' => $paid,
                'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
            ]);

            $rows = [];

            foreach ($testIds as $testId) {
                $test = $tests->get($testId);

                if ($test === null) {
                    continue;
                }

                $rows[] = PatientTest::query()->create([
                    'visit_id' => $visit->id,
                    'patient_id' => $patient->id,
                    'test_id' => $test->id,
                    'total_price' => count($testIds) === 1 && isset($validated['total_price'])
                        ? $validated['total_price']
                        : $test->price,
                    'paid' => $paid,
                    'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
                ]);
            }

            if ($paid) {
                $ledger->recordVisitPayment($visit->fresh());
            }

            return $rows;
        });

        if (count($created) === 1) {
            return redirect()
                ->route('patient-tests.show', $created[0])
                ->with('success', __('Test assigned to the patient.'));
        }

        return redirect()
            ->route('patients.show', $validated['patient_id'])
            ->with('success', __('Tests assigned to the patient.'));
    }

    public function show(PatientTest $patientTest): View
    {
        $patientTest->load(['patient', 'test', 'visit']);

        $testResult = TestResult::query()
            ->where('patient_id', $patientTest->patient_id)
            ->where('test_id', $patientTest->test_id)
            ->when($patientTest->visit_id, fn ($query) => $query->where('visit_id', $patientTest->visit_id))
            ->first();

        return view('patient-tests.show', [
            'patientTest' => $patientTest,
            'testResult' => $testResult,
        ]);
    }

    public function edit(PatientTest $patientTest): View
    {
        return view('patient-tests.edit', [
            'patientTest' => $patientTest,
            'patients' => Patient::query()->orderBy('name')->get(),
            'tests' => Test::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePatientTestRequest $request, PatientTest $patientTest): RedirectResponse
    {
        $patientTest->update($request->safe()->only([
            'patient_id',
            'test_id',
            'total_price',
            'paid',
        ]));

        if ($patientTest->paid && $patientTest->status === VisitStatus::Registered) {
            $patientTest->update(['status' => VisitStatus::Paid]);
        }

        return redirect()
            ->route('patient-tests.show', $patientTest)
            ->with('success', __('Assigned test updated.'));
    }

    public function destroy(PatientTest $patientTest): RedirectResponse
    {
        $patientTest->delete();

        return redirect()
            ->route('patient-tests.index')
            ->with('success', __('Assigned test deleted.'));
    }
}
