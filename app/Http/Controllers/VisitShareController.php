<?php

namespace App\Http\Controllers;

use App\Mail\VisitReportMail;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class VisitShareController extends Controller
{
    public function whatsapp(Visit $visit): SymfonyRedirectResponse|RedirectResponse
    {
        $visit->load('patient');

        $url = $visit->whatsappUrl();

        if ($url === null) {
            return back()->with('error', __('This patient has no phone number for WhatsApp.'));
        }

        return redirect()->away($url);
    }

    public function email(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $visit->load([
            'patient',
            'doctor',
            'patientTests.test',
            'testResults.test',
        ]);

        Mail::to($validated['email'])->send(new VisitReportMail($visit));

        return back()->with('success', __('Report emailed.'));
    }
}
