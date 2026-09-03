<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Models\PatientTest;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;

class PatientTestPaymentController extends Controller
{
    public function update(PatientTest $patientTest, CashLedger $ledger): RedirectResponse
    {
        $values = ['paid' => true];

        if (in_array($patientTest->status, [VisitStatus::Registered, VisitStatus::Paid], true)) {
            $values['status'] = VisitStatus::Paid;
        }

        $patientTest->update($values);

        $patientTest->load('visit.patientTests');

        $visit = $patientTest->visit;

        if ($visit !== null) {
            $visit->load('patientTests');

            $paidAmount = (float) $visit->patientTests->where('paid', true)->sum('total_price');
            $allPaid = $visit->patientTests->every(fn (PatientTest $row): bool => $row->paid);

            $visit->update([
                'paid_amount' => min($paidAmount, (float) $visit->total),
                'paid' => $allPaid,
                'status' => $allPaid && in_array($visit->status, [VisitStatus::Registered, VisitStatus::Paid], true)
                    ? VisitStatus::Paid
                    : $visit->status,
            ]);

            $ledger->recordVisitPayment($visit->fresh());
        }

        return back()->with('success', __('Payment marked as received.'));
    }
}
