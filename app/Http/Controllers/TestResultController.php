<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\StoreTestResultRequest;
use App\Http\Requests\UpdateTestResultRequest;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TestResultController extends Controller
{
    public function index(): View
    {
        return view('test-results.index', [
            'testResults' => TestResult::query()
                ->with(['patient', 'test'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('test-results.create', [
            'patients' => Patient::query()->orderBy('name')->get(),
            'tests' => Test::query()->with('parameters')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTestResultRequest $request): RedirectResponse
    {
        $testResult = DB::transaction(function () use ($request): TestResult {
            $patientTest = PatientTest::query()
                ->where('patient_id', $request->integer('patient_id'))
                ->where('test_id', $request->integer('test_id'))
                ->latest('id')
                ->first();

            $payload = $request->safe()->except('sensitivities');
            $payload['visit_id'] = $patientTest?->visit_id;

            $testResult = TestResult::query()->create($payload);

            $this->syncSensitivities($testResult, $request->validated('sensitivities', []));

            PatientTest::query()
                ->where('patient_id', $testResult->patient_id)
                ->where('test_id', $testResult->test_id)
                ->when($testResult->visit_id, fn ($query) => $query->where('visit_id', $testResult->visit_id))
                ->whereNot('status', VisitStatus::Delivered)
                ->update(['status' => VisitStatus::Completed]);

            if ($patientTest?->visit_id) {
                $visit = $patientTest->visit()->with(['patientTests', 'testResults'])->first();

                if ($visit?->isComplete() && $visit->status !== VisitStatus::Delivered) {
                    $visit->update(['status' => VisitStatus::Completed]);
                } elseif ($visit && in_array($visit->status, [VisitStatus::Registered, VisitStatus::Paid], true)) {
                    $visit->update(['status' => VisitStatus::InLab]);
                }
            }

            return $testResult;
        });

        return redirect()
            ->route('test-results.show', $testResult)
            ->with('success', __('Result saved successfully.'));
    }

    public function show(TestResult $testResult): View
    {
        $testResult->load(['patient', 'test.parameters', 'sensitivities']);

        return view('test-results.show', ['testResult' => $testResult]);
    }

    public function edit(TestResult $testResult): View
    {
        $testResult->load('sensitivities');

        return view('test-results.edit', [
            'testResult' => $testResult,
            'patients' => Patient::query()->orderBy('name')->get(),
            'tests' => Test::query()->with('parameters')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTestResultRequest $request, TestResult $testResult): RedirectResponse
    {
        DB::transaction(function () use ($request, $testResult): void {
            $testResult->update($request->safe()->except('sensitivities'));
            $this->syncSensitivities($testResult, $request->validated('sensitivities', []));
        });

        return redirect()
            ->route('test-results.show', $testResult)
            ->with('success', __('Result updated.'));
    }

    public function destroy(TestResult $testResult): RedirectResponse
    {
        DB::transaction(function () use ($testResult): void {
            $testResult->values()->delete();
            $testResult->sensitivities()->delete();
            $testResult->delete();
        });

        return redirect()
            ->route('test-results.index')
            ->with('success', __('Result deleted.'));
    }

    /**
     * @param  array<int, array{antibiotic: string, sensitivity: string}>  $sensitivities
     */
    private function syncSensitivities(TestResult $testResult, array $sensitivities): void
    {
        $testResult->sensitivities()->delete();

        foreach (array_values($sensitivities) as $index => $row) {
            if (($row['antibiotic'] ?? '') === '') {
                continue;
            }

            $testResult->sensitivities()->create([
                'antibiotic' => $row['antibiotic'],
                'sensitivity' => $row['sensitivity'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
