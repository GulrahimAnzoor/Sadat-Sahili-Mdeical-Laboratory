<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashTransactionRequest;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;

class CashTransactionController extends Controller
{
    public function store(StoreCashTransactionRequest $request, CashLedger $ledger): RedirectResponse
    {
        $ledger->record($request->validated());

        return back()->with('success', __('Transaction saved.'));
    }
}
