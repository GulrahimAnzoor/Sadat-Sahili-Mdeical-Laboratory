<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestParameterRequest;
use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Http\RedirectResponse;

class TestParameterController extends Controller
{
    public function store(StoreTestParameterRequest $request, Test $test): RedirectResponse
    {
        $test->parameters()->create($request->validated());

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test information added.'));
    }

    public function destroy(Test $test, TestParameter $parameter): RedirectResponse
    {
        abort_unless($parameter->test_id === $test->id, 404);

        $parameter->delete();

        return redirect()
            ->route('tests.show', $test)
            ->with('success', __('Test information removed.'));
    }
}
