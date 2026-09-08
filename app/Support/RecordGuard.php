<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Supplier;
use App\Models\Test;
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
        if ($test->patientTests()->exists() || $test->testResults()->exists()) {
            throw ValidationException::withMessages([
                'test' => __('This test has been used on patient records and cannot be deleted.'),
            ]);
        }
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
