<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->safe()->only(['name', 'slug', 'description', 'permissions']);
        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        if (Role::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        Role::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'permissions' => array_values($validated['permissions'] ?? []),
        ]);

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Role saved.'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'permissions' => array_values($request->validated('permissions') ?? []),
        ]);

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Role updated.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Role deleted.'));
    }
}
