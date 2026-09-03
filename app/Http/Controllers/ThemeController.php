<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class ThemeController extends Controller
{
    public function __invoke(string $theme): RedirectResponse
    {
        abort_unless(in_array($theme, (array) config('lab.themes'), true), 404);

        session(['theme' => $theme]);

        return back();
    }
}
