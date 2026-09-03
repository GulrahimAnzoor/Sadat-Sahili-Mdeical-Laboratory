<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()?->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('password.edit')
            ->with('success', __('Password updated.'));
    }
}
