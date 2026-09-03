<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'patients' => Patient::query()
                ->with('doctor')
                ->withCount(['patientTests', 'testResults', 'visits'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'doctor',
            'patientTests.test.parameters',
            'testResults.test.parameters',
            'testResults.values.parameter',
            'testResults.sensitivities',
        ]);

        $resultsByTestId = $patient->testResults->keyBy('test_id');

        return view('reports.show', [
            'patient' => $patient,
            'resultsByTestId' => $resultsByTestId,
        ]);
    }
}
