<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Support\RecordGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        return view('doctors.index', [
            'doctors' => Doctor::query()
                ->withCount('patients')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('doctors.create');
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $doctor = Doctor::query()->create($request->validated());

        return redirect()
            ->route('doctors.show', $doctor)
            ->with('success', __('Doctor saved successfully.'));
    }

    public function show(Doctor $doctor): View
    {
        $doctor->load(['patients' => fn ($query) => $query->withCount(['patientTests', 'testResults'])->latest()]);

        return view('doctors.show', ['doctor' => $doctor]);
    }

    public function edit(Doctor $doctor): View
    {
        return view('doctors.edit', ['doctor' => $doctor]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $doctor->update($request->validated());

        return redirect()
            ->route('doctors.show', $doctor)
            ->with('success', __('Doctor updated successfully.'));
    }

    public function destroy(Doctor $doctor, RecordGuard $guard): RedirectResponse
    {
        $guard->ensureDoctorCanBeDeleted($doctor);

        $doctor->delete();

        return redirect()
            ->route('doctors.index')
            ->with('success', __('Doctor deleted.'));
    }
}
