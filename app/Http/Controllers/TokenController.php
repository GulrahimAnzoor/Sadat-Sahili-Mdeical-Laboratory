<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function show(Request $request, Patient $patient): View|RedirectResponse
    {
        $visit = Visit::query()
            ->whereBelongsTo($patient)
            ->latest('id')
            ->first();

        if ($visit !== null) {
            return redirect()->route('visits.token', [
                'visit' => $visit,
                'size' => $request->string('size')->toString() ?: null,
            ]);
        }

        $size = $request->string('size')->toString();

        if (! in_array($size, ['a5', 'a6'], true)) {
            $size = 'a6';
        }

        $patient->load(['doctor', 'patientTests.test']);

        return view('tokens.show', [
            'visit' => null,
            'patient' => $patient,
            'size' => $size,
        ]);
    }
}
