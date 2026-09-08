<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', [
            'patients' => Patient::query()
                ->with('doctor')
                ->withCount(['patientTests', 'testResults', 'visits'])
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $term = '%'.$request->string('q').'%';
                    $query->where(function ($nested) use ($term): void {
                        $nested->where('name', 'like', $term)
                            ->orWhere('father_name', 'like', $term)
                            ->orWhere('file_number', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
                })
                ->latest()
                ->paginate(15)
                ->withQueryString(),
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
