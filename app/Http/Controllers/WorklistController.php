<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorklistController extends Controller
{
    public function __invoke(Request $request): View
    {
        $visits = Visit::query()
            ->with([
                'patient.doctor',
                'doctor',
                'patientTests.test',
                'testResults',
            ])
            ->whereHas('patientTests')
            ->whereNot('status', VisitStatus::Delivered)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($nested) use ($term): void {
                    $nested->where('token_code', 'like', $term)
                        ->orWhere('queue_number', 'like', $term)
                        ->orWhereHas('patient', function ($patient) use ($term): void {
                            $patient->where('name', 'like', $term)
                                ->orWhere('father_name', 'like', $term)
                                ->orWhere('file_number', 'like', $term)
                                ->orWhere('phone', 'like', $term);
                        })
                        ->orWhereHas('patientTests.test', function ($test) use ($term): void {
                            $test->where('name', 'like', $term);
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('worklist.index', [
            'visits' => $visits,
        ]);
    }
}
