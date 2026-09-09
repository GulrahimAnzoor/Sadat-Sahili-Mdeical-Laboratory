<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Enums\VisitStatus;
use App\Http\Requests\StoreVisitResultsRequest;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\Visit;
use App\Services\StockLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitResultController extends Controller
{
    public function edit(Visit $visit): View
    {
        $visit->load([
            'patient.doctor',
            'doctor',
            'patientTests.test.parameters',
            'testResults.values',
            'testResults.test',
        ]);

        $resultsByTestId = $visit->testResults->keyBy('test_id');
        $requestedTestId = (int) request('test');
        $activeTestId = $visit->patientTests->firstWhere('id', $requestedTestId)?->id
            ?? $visit->patientTests->first(fn ($patientTest) => ! $resultsByTestId->has($patientTest->test_id))?->id
            ?? $visit->patientTests->first()?->id;

        $materialsByPatientTestId = StockMovement::query()
            ->with('inventoryItem:id,name,quantity,batch_number,expires_on')
            ->where('type', StockMovementType::OutTest)
            ->whereIn('patient_test_id', $visit->patientTests->pluck('id'))
            ->orderBy('id')
            ->get()
            ->groupBy(fn (StockMovement $movement): int => (int) $movement->patient_test_id);

        return view('visits.results', [
            'visit' => $visit,
            'resultsByTestId' => $resultsByTestId,
            'activeTestId' => $activeTestId,
            'stockLots' => InventoryItem::query()
                ->inStock()
                ->orderBy('name')
                ->orderBy('expires_on')
                ->orderBy('id')
                ->get(['id', 'name', 'quantity', 'batch_number', 'expires_on']),
            'materialsByPatientTestId' => $materialsByPatientTestId,
        ]);
    }

    public function store(StoreVisitResultsRequest $request, Visit $visit, StockLedger $stock): RedirectResponse
    {
        DB::transaction(function () use ($request, $visit, $stock): void {
            $visit->load(['patientTests.test.parameters', 'testResults']);

            foreach ($request->validated('results') as $row) {
                $patientTest = $visit->patientTests->firstWhere('id', (int) $row['patient_test_id']);

                if ($patientTest === null || $patientTest->test === null) {
                    continue;
                }

                $test = $patientTest->test;
                $extraRows = $row['extra_rows'] ?? [];
                $hasExtras = collect($extraRows)->contains(
                    fn (array $extra): bool => filled($extra['name'] ?? null) || filled($extra['value'] ?? null),
                );

                if ($hasExtras && $test->parameters->isEmpty() && filled($row['result'] ?? null)) {
                    array_unshift($extraRows, [
                        'name' => $test->name,
                        'value' => $row['result'],
                        'unit' => $row['unit'] ?? '',
                        'normal_range' => $test->normal_range,
                    ]);
                }

                $createdValues = $this->createParametersFromExtraRows($test, $extraRows);
                $test->load('parameters');

                $values = collect($row['values'] ?? [])
                    ->concat($createdValues)
                    ->filter(fn (array $value): bool => filled($value['value'] ?? null))
                    ->values();

                $overall = $row['result'] ?? data_get($values->first(), 'value');
                $hasMaterials = (bool) ($row['consume_materials'] ?? false);
                $materials = $row['materials'] ?? [];

                if (! filled($overall) && $values->isEmpty() && ! $hasMaterials) {
                    continue;
                }

                if (filled($overall) || $values->isNotEmpty()) {
                    $unit = $row['unit']
                        ?? $test->parameters->first()?->unit
                        ?? '';

                    $testResult = TestResult::query()->updateOrCreate(
                        [
                            'visit_id' => $visit->id,
                            'patient_id' => $visit->patient_id,
                            'test_id' => $patientTest->test_id,
                        ],
                        [
                            'result' => (string) ($overall ?? ''),
                            'unit' => (string) $unit,
                        ],
                    );

                    $testResult->values()->delete();

                    foreach ($values as $valueRow) {
                        $testResult->values()->create([
                            'test_parameter_id' => $valueRow['test_parameter_id'],
                            'value' => $valueRow['value'],
                        ]);
                    }

                    if ($patientTest->status !== VisitStatus::Delivered) {
                        $patientTest->update(['status' => VisitStatus::Completed]);
                    }
                }

                if ($hasMaterials) {
                    $stock->replaceTestConsumption(
                        $patientTest,
                        $visit,
                        $materials,
                        $request->user()?->id,
                    );
                }
            }

            $visit->unsetRelation('patientTests');
            $visit->unsetRelation('testResults');
            $visit->load(['patientTests', 'testResults']);

            if ($visit->isComplete() && $visit->status !== VisitStatus::Delivered) {
                $visit->update(['status' => VisitStatus::Completed]);
            } elseif ($visit->status === VisitStatus::Paid || $visit->status === VisitStatus::Registered) {
                $visit->update(['status' => VisitStatus::InLab]);
            }
        });

        $patientTestId = (int) ($request->validated('results.0.patient_test_id') ?? 0);

        return redirect()
            ->route('visits.results.edit', [
                'visit' => $visit,
                'test' => $patientTestId > 0 ? $patientTestId : null,
            ])
            ->with('success', __('Results saved.'));
    }

    /**
     * @param  list<array{name?: string|null, value?: string|null, unit?: string|null, normal_range?: string|null}>  $rows
     * @return list<array{test_parameter_id: int, value: string}>
     */
    private function createParametersFromExtraRows(Test $test, array $rows): array
    {
        $nextOrder = (int) $test->parameters->max('sort_order');
        $created = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            $unit = trim((string) ($row['unit'] ?? ''));
            $range = trim((string) ($row['normal_range'] ?? ''));

            if ($name === '' && $value === '') {
                continue;
            }

            $parameterName = $name !== '' ? $name : __('Result').' '.($nextOrder + 1);
            $parameter = $test->parameters
                ->first(fn ($existing): bool => strcasecmp($existing->name, $parameterName) === 0);

            if ($parameter === null) {
                $nextOrder++;

                $parameter = $test->parameters()->create([
                    'name' => $parameterName,
                    'unit' => $unit !== '' ? $unit : null,
                    'normal_range' => $range !== '' ? $range : null,
                    'sort_order' => $nextOrder,
                ]);

                $test->parameters->push($parameter);
            } else {
                $updates = [];

                if ($unit !== '' && $parameter->unit !== $unit) {
                    $updates['unit'] = $unit;
                }

                if ($range !== '' && $parameter->normal_range !== $range) {
                    $updates['normal_range'] = $range;
                }

                if ($updates !== []) {
                    $parameter->update($updates);
                }
            }

            $created[] = [
                'test_parameter_id' => $parameter->id,
                'value' => $value,
            ];
        }

        return $created;
    }

    public function report(Visit $visit): View
    {
        $visit->load([
            'patient.doctor',
            'doctor',
            'patientTests.test.parameters',
            'testResults.test.parameters',
            'testResults.values.parameter',
            'testResults.sensitivities',
        ]);

        $resultsByTestId = $visit->testResults->keyBy('test_id');

        return view('visits.report', [
            'visit' => $visit,
            'patient' => $visit->patient,
            'resultsByTestId' => $resultsByTestId,
        ]);
    }
}
