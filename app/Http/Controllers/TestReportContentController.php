<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTestReportContentRequest;
use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TestReportContentController extends Controller
{
    public function edit(?Test $test = null): View
    {
        $test?->load('parameters');

        return view('settings.test-reports', [
            'tests' => Test::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'interpretation']),
            'selected' => $test,
        ]);
    }

    public function update(UpdateTestReportContentRequest $request, Test $test): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($test, $validated): void {
            $test->update([
                'normal_range' => $validated['normal_range'],
                'interpretation' => $validated['interpretation'] ?? null,
            ]);

            foreach ($validated['parameters'] ?? [] as $row) {
                TestParameter::query()
                    ->whereKey($row['id'])
                    ->where('test_id', $test->id)
                    ->update([
                        'normal_range' => filled($row['normal_range'] ?? null) ? $row['normal_range'] : null,
                    ]);
            }
        });

        return redirect()
            ->route('settings.test-reports.edit', $test)
            ->with('success', __('Report content saved.'));
    }
}
