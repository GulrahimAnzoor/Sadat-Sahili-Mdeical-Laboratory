<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Page not found') }}</title>
        <style>
            body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: #f1f5f9; color: #0f172a; }
            main { max-width: 32rem; margin: 12vh auto; padding: 2rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; }
            h1 { margin: 0 0 0.75rem; font-size: 1.35rem; }
            p { margin: 0; line-height: 1.6; color: #475569; }
            a { display: inline-block; margin-top: 1.25rem; color: #0f766e; font-weight: 600; }
        </style>
    </head>
    <body>
        <main>
            <h1>{{ __('This page is not available.') }}</h1>
            <p>{{ __('Use the menu to open patients, reception, or the laboratory worklist.') }}</p>
            <a href="{{ url('/') }}">{{ __('Back to the laboratory') }}</a>
        </main>
    </body>
</html>
