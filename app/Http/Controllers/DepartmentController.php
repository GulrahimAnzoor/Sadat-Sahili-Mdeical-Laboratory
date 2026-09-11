<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class DepartmentController extends Controller
{
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $name = $request->validated('name');

        Department::query()->create([
            'name' => $name,
            'slug' => Department::uniqueSlug($name),
            'sort_order' => ((int) Department::query()->max('sort_order')) + 1,
        ]);

        Department::flushCatalog();

        return back()->with('success', __('Department added.'));
    }
}
