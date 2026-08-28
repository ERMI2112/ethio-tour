<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #0d3824;
            --brand-green-soft: #14532d;
            --brand-gold: #e5a919;
            --ink: #12233b;
            --muted: #5c6b7a;
            --surface: #f6f4ee;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: var(--surface);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            text-align: center;
        }
        .error-mark { width: 72px; height: 72px; margin-bottom: 1.5rem; }
        .error-kicker {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--brand-gold);
            font-size: 0.8rem;
            margin: 0 0 0.75rem;
        }
        .error-code {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: clamp(3.5rem, 10vw, 5.5rem);
            line-height: 1;
            color: var(--brand-green);
            margin: 0 0 0.75rem;
        }
        .error-title {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: clamp(1.25rem, 3.5vw, 1.75rem);
            margin: 0 0 0.75rem;
        }
        .error-message {
            color: var(--muted);
            max-width: 32rem;
            line-height: 1.65;
            margin: 0 auto 2rem;
        }
        .error-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; }
        .error-btn {
            display: inline-block;
            padding: 0.7rem 1.6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .error-btn:focus-visible { outline: 3px solid var(--brand-gold); outline-offset: 2px; }
        .error-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(13, 56, 36, 0.18); }
        .error-btn--primary { background: var(--brand-green); color: #fff; }
        .error-btn--ghost { border: 2px solid var(--brand-green); color: var(--brand-green); background: transparent; }
        .error-footer { margin-top: 3rem; color: var(--muted); font-size: 0.8rem; }
        .error-footer a { color: var(--brand-green-soft); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <img src="{{ asset('favicon.svg') }}" alt="" class="error-mark" width="72" height="72">
    @yield('body')
    <p class="error-footer">Ethio Tour &middot; Land of Origins &middot; <a href="{{ url('/') }}">ethio-tour</a></p>
</body>
</html>
