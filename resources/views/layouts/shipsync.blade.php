<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0f172a;
            --panel: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --ok: #4ade80;
            --warn: #fbbf24;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Instrument Sans', system-ui, sans-serif;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 100%);
            color: var(--text);
            min-height: 100vh;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .wrap { max-width: 960px; margin: 0 auto; padding: 2rem 1.25rem 3rem; }
        header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; }
        header h1 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; }
        nav { display: flex; gap: 1rem; font-size: 0.95rem; }
        .panel {
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        label { display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 0.35rem; }
        input, button {
            font: inherit;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.65rem 0.75rem;
            width: 100%;
        }
        input { background: #0f172a; color: var(--text); }
        button {
            background: var(--accent);
            color: #0f172a;
            font-weight: 600;
            border: none;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .status { font-size: 0.9rem; color: var(--muted); margin-top: 1rem; }
        .status.ok { color: var(--ok); }
        .status.warn { color: var(--warn); }
        pre {
            background: #0f172a;
            padding: 1rem;
            border-radius: 8px;
            overflow: auto;
            font-size: 0.8rem;
            line-height: 1.45;
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="wrap">
        <header>
            <h1>ShipSync Hub</h1>
            <nav>
                <a href="{{ route('shipping.quotes.ui') }}">Cotações</a>
                <a href="{{ route('openapi.ui') }}">API (Swagger)</a>
            </nav>
        </header>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
