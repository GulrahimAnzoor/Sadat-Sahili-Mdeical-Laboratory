<?php

namespace App\Support;

use App\Enums\VisitStatus;
use App\Models\Account;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Supplier;
use App\Models\Test;
use App\Models\TestParameter;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

class RecordGuard
{
    public function ensurePatientCanBeDeleted(Patient $patient): void
    {
        if ($patient->patientTests()->exists() || $patient->testResults()->exists() || $patient->visits()->exists()) {
            throw ValidationException::withMessages([
                'patient' => __('This patient has laboratory records and cannot be deleted.'),
            ]);
        }
    }

    public function ensureDoctorCanBeDeleted(Doctor $doctor): void
    {
        if ($doctor->patients()->exists() || $doctor->visits()->exists()) {
            throw ValidationException::withMessages([
                'doctor' => __('This doctor is linked to laboratory records and cannot be deleted.'),
            ]);
        }
    }

    public function ensureTestCanBeDeleted(Test $test): void
    {
        if ($test->testResults()->exists()) {
            throw ValidationException::withMessages([
                'test' => __('This test has been used on patient records and cannot be deleted.'),
            ]);
        }

        $assignments = $test->patientTests()->with('visit')->get();

        foreach ($assignments as $assignment) {
            if ($this->assignmentIsHistorical($assignment)) {
                throw ValidationException::withMessages([
                    'test' => __('This test has been used on patient records and cannot be deleted.'),
                ]);
            }
        }
    }

    public function ensurePatientTestCanBeDeleted(PatientTest $patientTest): void
    {
        if ($this->assignmentIsHistorical($patientTest)) {
            throw ValidationException::withMessages([
                'patient_test' => __('This assigned test is billed or completed and cannot be deleted.'),
            ]);
        }
    }

    public function ensureTestParameterCanBeDeleted(TestParameter $parameter): void
    {
        if ($parameter->resultValues()->exists()) {
            throw ValidationException::withMessages([
                'parameter' => __('This test information is used on recorded results and cannot be deleted.'),
            ]);
        }
    }

    private function assignmentIsHistorical(PatientTest $patientTest): bool
    {
        if ($patientTest->paid) {
            return true;
        }

        $visit = $patientTest->visit;

        return $visit instanceof Visit && in_array($visit->status, [
            VisitStatus::Paid,
            VisitStatus::Completed,
            VisitStatus::Delivered,
        ], true);
    }

    public function ensureAccountCanBeDeleted(Account $account): void
    {
        if ($account->cashTransactions()->exists()) {
            throw ValidationException::withMessages([
                'account' => __('This cash account has transactions and cannot be deleted.'),
            ]);
        }
    }

    public function ensureSupplierCanBeDeleted(Supplier $supplier): void
    {
        if ($supplier->purchases()->exists()) {
            throw ValidationException::withMessages([
                'supplier' => __('This supplier has purchase records and cannot be deleted.'),
            ]);
        }
    }
}
