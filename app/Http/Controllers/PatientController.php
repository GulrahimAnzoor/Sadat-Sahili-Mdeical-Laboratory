<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\LabAlerts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $patients = Patient::query()
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
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
        ]);
    }

    public function create(): View
    {
        return view('patients.create', [
            'doctors' => Doctor::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = Patient::query()->create($request->validated());

        LabAlerts::patientRegistered($patient);

        return redirect()
            ->route('reception.visit', $patient)
            ->with('success', __('Patient saved successfully.'));
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'doctor',
            'visits.doctor',
            'visits.patientTests.test',
            'visits.testResults.test',
            'patientTests.test',
            'testResults.test',
        ]);

        $resultsByTestId = $patient->testResults->keyBy('test_id');

        return view('patients.show', [
            'patient' => $patient,
            'resultsByTestId' => $resultsByTestId,
        ]);
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', [
            'patient' => $patient,
            'doctors' => Doctor::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('Patient updated successfully.'));
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', __('Patient deleted.'));
    }
}
