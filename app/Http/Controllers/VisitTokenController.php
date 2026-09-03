<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitTokenController extends Controller
{
    public function show(Request $request, Visit $visit): View
    {
        $size = $request->string('size')->toString();

        if (! in_array($size, ['a5', 'a6'], true)) {
            $size = 'a6';
        }

        $visit->load(['patient', 'doctor', 'patientTests.test']);

        return view('tokens.show', [
            'visit' => $visit,
            'patient' => $visit->patient,
            'size' => $size,
        ]);
    }
}
