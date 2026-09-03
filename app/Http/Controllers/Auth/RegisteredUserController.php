<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterFirstUserRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function store(RegisterFirstUserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            if (User::query()->lockForUpdate()->exists()) {
                abort(403);
            }

            $validated = $request->validated();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $manager = Role::query()->firstWhere('slug', 'manager');

            Staff::query()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'role_id' => $manager?->id,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
