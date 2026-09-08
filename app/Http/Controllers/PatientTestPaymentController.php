<?php

namespace App\Http\Controllers;

use App\Models\PatientTest;
use App\Support\CashLedger;
use App\Support\VisitBilling;
use Illuminate\Http\RedirectResponse;

class PatientTestPaymentController extends Controller
{
    public function update(PatientTest $patientTest, VisitBilling $billing, CashLedger $ledger): RedirectResponse
    {
        $billing->markTestPaid($patientTest, $ledger);

        return back()->with('success', __('Payment marked as received.'));
    }
}
