<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0b141d">
    <title>@yield('code') · @yield('title') | {{ config('app.name', 'Free Games') }}</title>
    <style>
        :root{color-scheme:dark;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#08111b;color:#e8eef6}.card{width:min(100%,650px);padding:40px;border:1px solid #233244;border-radius:22px;background:#0f1c29;box-shadow:0 24px 70px rgba(0,0,0,.35)}.code{font-size:.85rem;font-weight:800;letter-spacing:.18em;color:#60a5fa;text-transform:uppercase}h1{font-size:clamp(2rem,6vw,3.4rem);margin:.55rem 0 1rem}p{color:#aab8c8;line-height:1.7;margin:0 0 1.7rem}.actions{display:flex;gap:12px;flex-wrap:wrap}a{display:inline-flex;padding:11px 16px;border-radius:10px;text-decoration:none;font-weight:700;background:#2563eb;color:#fff}a.secondary{background:#17283a;color:#dce8f5}@media(max-width:520px){.card{padding:28px}.actions a{width:100%;justify-content:center}}
    </style>
</head>
<body>
<main class="card">
    <div class="code">Error @yield('code')</div>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <div class="actions"><a href="{{ url('/games') }}">Browse games</a><a class="secondary" href="{{ url('/') }}">Main site</a></div>
</main>
</body>
</html>
