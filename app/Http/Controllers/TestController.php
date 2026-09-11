<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestRequest;
use App\Http\Requests\UpdateTestRequest;
use App\Models\Department;
use App\Models\Test;
use App\Models\Visit;
use App\Support\RecordGuard;
use App\Support\VisitBilling;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TestController extends Controller
{
    public function index(Request $request): View
    {
        $department = $request->string('department')->toString();

        $tests = Test::query()
            ->withCount(['patientTests', 'testResults'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('q').'%');
            })
            ->when(
                $department !== '' && Department::query()->where('slug', $department)->exists(),
                fn ($query) => $query->where('department', $department),
            )
            ->orderBy('department')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tests.index', [
            'tests' => $tests,
            'departments' => Department::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('tests.create', [
            'departments' => Department::query()->ordered()->get(),
        ]);
    }

    public function store(StoreTestRequest $request): RedirectResponse
    {
        $test = Test::query()->create($request->validated());

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test saved successfully.'));
    }

    public function show(Test $test): View
    {
        $test->loadCount(['patientTests', 'testResults']);
        $test->load([
            'parameters',
            'testResults' => fn ($query) => $query->with('patient')->latest()->limit(8),
        ]);

        return view('tests.show', ['test' => $test]);
    }

    public function edit(Test $test): View
    {
        return view('tests.edit', [
            'test' => $test,
            'departments' => Department::query()->ordered()->get(),
        ]);
    }

    public function update(UpdateTestRequest $request, Test $test): RedirectResponse
    {
        $test->update($request->validated());

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test updated successfully.'));
    }

    public function destroy(Test $test, RecordGuard $guard, VisitBilling $billing): RedirectResponse
    {
        $guard->ensureTestCanBeDeleted($test);

        try {
            DB::transaction(function () use ($test, $billing): void {
                $visitIds = $test->patientTests()
                    ->pluck('visit_id')
                    ->filter()
                    ->unique()
                    ->values();

                $test->patientTests()->delete();
                $test->parameters()->delete();
                $test->delete();

                Visit::query()->whereKey($visitIds)->get()->each(
                    fn (Visit $visit) => $billing->recalculate($visit),
                );
            });
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'test' => __('This test has been used on patient records and cannot be deleted.'),
            ]);
        }

        return redirect()
            ->route('tests.index')
            ->with('success', __('Test deleted.'));
    }
}
