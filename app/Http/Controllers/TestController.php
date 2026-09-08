<?php

namespace App\Http\Controllers;

use App\Enums\TestDepartment;
use App\Http\Requests\StoreTestRequest;
use App\Http\Requests\UpdateTestRequest;
use App\Models\Test;
use App\Support\RecordGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                $department !== '' && TestDepartment::tryFrom($department) !== null,
                fn ($query) => $query->where('department', $department),
            )
            ->orderBy('department')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tests.index', [
            'tests' => $tests,
            'departments' => TestDepartment::cases(),
        ]);
    }

    public function create(): View
    {
        return view('tests.create', [
            'departments' => TestDepartment::cases(),
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
            'departments' => TestDepartment::cases(),
        ]);
    }

    public function update(UpdateTestRequest $request, Test $test): RedirectResponse
    {
        $test->update($request->validated());

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test updated successfully.'));
    }

    public function destroy(Test $test, RecordGuard $guard): RedirectResponse
    {
        $guard->ensureTestCanBeDeleted($test);

        $test->delete();

        return redirect()
            ->route('tests.index')
            ->with('success', __('Test deleted.'));
    }
}
