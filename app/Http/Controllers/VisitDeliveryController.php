<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Http\Requests\DeliverVisitRequest;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VisitDeliveryController extends Controller
{
    public function store(DeliverVisitRequest $request, Visit $visit): RedirectResponse
    {
        $visit->update([
            'delivered_to' => $request->validated('delivered_to'),
            'delivery_box' => $request->validated('delivery_box'),
            'delivered_at' => now(),
            'status' => VisitStatus::Delivered,
        ]);

        $visit->patientTests()->whereNot('status', VisitStatus::Delivered)->update([
            'status' => VisitStatus::Delivered,
        ]);

        return redirect()
            ->route('visits.handover', $visit)
            ->with('success', __('Report handed over.'));
    }

    public function show(Visit $visit): View
    {
        $visit->load(['patient', 'doctor', 'patientTests.test']);

        return view('visits.handover', [
            'visit' => $visit,
            'patient' => $visit->patient,
        ]);
    }
}
