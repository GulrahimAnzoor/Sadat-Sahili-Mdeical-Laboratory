<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('app.available_locales')), 404);

        session(['locale' => $locale]);

        return back();
    }
}
