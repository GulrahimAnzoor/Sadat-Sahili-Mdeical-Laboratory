<?php

namespace App\Http\Controllers;

use App\Support\FinancePeriodReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancePeriodReportController extends Controller
{
    public function show(Request $request, FinancePeriodReport $report): View
    {
        return view('finance.period-report', $report->build(
            $request->string('period')->toString(),
        ));
    }
}
