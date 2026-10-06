<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Page unavailable') | {{ config('app.name') }}</title>
    <style>
        :root{color-scheme:dark;--bg:#0b141d;--panel:#111827;--text:#f8fafc;--muted:#cbd5e1;--accent:#38bdf8;--border:rgba(148,163,184,.28)}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:24px}
        main{width:min(680px,100%);background:var(--panel);border:1px solid var(--border);border-radius:8px;padding:32px;box-shadow:0 24px 60px rgba(0,0,0,.24)}
        .code{color:var(--accent);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:13px}h1{font-size:clamp(28px,4vw,42px);line-height:1.1;margin:10px 0 12px}p{color:var(--muted);font-size:16px;line-height:1.6;margin:0 0 22px}
        .actions{display:flex;gap:12px;flex-wrap:wrap}a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border-radius:6px;text-decoration:none;font-weight:700}
        .primary{background:#f8fafc;color:#111827}.secondary{border:1px solid var(--border);color:var(--text)}
    </style>
</head>
<body>
<main>
    <div class="code">@yield('code')</div>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>
    <div class="actions">
        <a class="primary" href="{{ route('catalog.index') }}">Game catalog</a>
        <a class="secondary" href="{{ url('/') }}">Home</a>
    </div>
</main>
</body>
</html>
