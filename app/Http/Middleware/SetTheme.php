<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetTheme
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $themes = (array) config('lab.themes');
        $theme = $request->session()->get('theme', config('lab.default_theme'));

        if (! in_array($theme, $themes, true)) {
            $theme = config('lab.default_theme');
        }

        View::share('theme', $theme);

        return $next($request);
    }
}
