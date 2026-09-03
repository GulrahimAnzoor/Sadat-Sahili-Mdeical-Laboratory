<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Support\CashLedger;
use App\Support\VisitBilling;
use Illuminate\Http\RedirectResponse;

class VisitPaymentController extends Controller
{
    public function update(Visit $visit, VisitBilling $billing, CashLedger $ledger): RedirectResponse
    {
        $billing->collectRemaining($visit, $ledger);

        return back()->with('success', __('Payment marked as received.'));
    }
}
