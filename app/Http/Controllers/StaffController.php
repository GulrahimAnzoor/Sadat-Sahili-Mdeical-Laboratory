<?php

namespace App\Http\Controllers;

use App\Enums\LabPermission;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Account;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\StaffCredentials;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->withCount('staff')->orderBy('name')->get();

        return view('settings.staff', [
            'roles' => $roles,
            'staff' => Staff::query()->with(['role', 'account', 'user'])->latest('id')->get(),
            'administratorCount' => User::query()->where('is_admin', true)->count(),
            'accounts' => Account::query()->orderBy('name')->get(),
            'permissionGroups' => LabPermission::grouped(),
            'loginDomain' => Str::after((string) config('lab.email'), '@') ?: 'ssml.af',
            'rolePermissionLabels' => $roles->mapWithKeys(fn (Role $role): array => [
                (string) $role->id => collect($role->permissions ?? [])
                    ->map(fn (string $permission): ?string => LabPermission::tryFrom($permission)?->label())
                    ->filter()
                    ->values()
                    ->all(),
            ]),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $validated = $request->safe()->only([
            'name',
            'phone',
            'role_id',
            'account_id',
        ]);

        $credentials = StaffCredentials::make($validated['name']);

        DB::transaction(function () use ($validated, $credentials): void {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Staff::query()->create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'role_id' => $validated['role_id'],
                'account_id' => $validated['account_id'] ?? null,
            ]);
        });

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Staff member saved. Give them this login.'))
            ->with('generated_login', [
                'name' => $validated['name'],
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $validated = $request->safe()->only([
            'name',
            'phone',
            'role_id',
            'account_id',
            'email',
            'password',
        ]);

        DB::transaction(function () use ($staff, $validated): void {
            $staff->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'role_id' => $validated['role_id'] ?? null,
                'account_id' => $validated['account_id'] ?? null,
            ]);

            if ($staff->user === null) {
                return;
            }

            $userAttributes = [
                'name' => $validated['name'],
            ];

            if (filled($validated['email'] ?? null)) {
                $userAttributes['email'] = $validated['email'];
            }

            if (filled($validated['password'] ?? null)) {
                $userAttributes['password'] = $validated['password'];
            }

            $staff->user->update($userAttributes);
        });

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Staff member updated.'));
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        if ($staff->user_id !== null && $staff->user_id === auth()->id()) {
            throw ValidationException::withMessages([
                'staff' => __('You cannot delete your own login.'),
            ]);
        }

        if ($staff->user?->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            throw ValidationException::withMessages([
                'staff' => __('The last administrator cannot be deleted.'),
            ]);
        }

        DB::transaction(function () use ($staff): void {
            $user = $staff->user;
            $staff->delete();
            $user?->delete();
        });

        return redirect()
            ->route('settings.staff.index')
            ->with('success', __('Staff member deleted.'));
    }
}
