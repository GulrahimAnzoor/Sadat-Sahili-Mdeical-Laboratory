<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestParameterRequest;
use App\Models\Test;
use App\Models\TestParameter;
use App\Support\RecordGuard;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class TestParameterController extends Controller
{
    public function store(StoreTestParameterRequest $request, Test $test): RedirectResponse
    {
        $test->parameters()->create($request->validated());

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test information added.'));
    }

    public function destroy(Test $test, TestParameter $parameter, RecordGuard $guard): RedirectResponse
    {
        abort_unless($parameter->test_id === $test->id, 404);

        $guard->ensureTestParameterCanBeDeleted($parameter);

        try {
            $parameter->delete();
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'parameter' => __('This test information is used on recorded results and cannot be deleted.'),
            ]);
        }

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test information removed.'));
    }
}
