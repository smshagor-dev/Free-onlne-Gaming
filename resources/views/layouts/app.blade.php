<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $setting->name ?? 'Free Games' }}</title>
<meta name="description" content="Discover and play free browser games. Save favorites and explore our lottery.">
<meta name="csrf-token" content="{{ csrf_token() }}"><meta name="user-id" content="{{ auth()->id() }}">
<link rel="canonical" href="{{ url()->current() }}"><link rel="manifest" href="{{ asset('manifest.json') }}">
@if(auth()->check() || request()->is('user/*'))<meta name="robots" content="noindex, nofollow">@endif
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@vite('resources/js/app.js')
@stack('head')
<style>body{margin:0;background:#081421;color:#eef5ff;font:16px/1.5 system-ui,sans-serif}a{color:inherit;text-decoration:none}button,input,select{font:inherit}header{background:#102131;border-bottom:1px solid #26384c;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}nav{display:flex;gap:20px;flex-wrap:wrap}main{max-width:1440px;margin:auto;padding:24px;min-height:70vh}.account-panel{padding:24px;border:1px solid #26384c;border-radius:16px;background:#102131}.portal-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-top:24px}.portal-grid>a{padding:24px;background:#19334b;border-radius:12px}.portal-grid>a:hover{background:#245074}footer{padding:24px;text-align:center;color:#9db1c7}a:focus-visible,button:focus-visible{outline:3px solid #19aaff;outline-offset:4px}.status-message{padding:12px;background:#19334b;border-radius:8px;margin-bottom:16px}.home-main{max-width:none;padding:0}</style>
</head><body>
<header><a href="{{ route('games.viewIndex') }}"><strong>FreeGames</strong></a>
<nav aria-label="Main navigation"><a href="{{ route('playable.index') }}">Play Games</a><a href="{{ route('catalog.index') }}">Discover</a><a href="{{ route('free.games.index') }}">Categories</a><a href="{{ route('user.lottaries.view') }}">Lottery</a>
@auth<a href="{{ route('user.dashboard') }}">My Account</a><a href="{{ route('user.profile') }}">Profile</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
@else<a href="{{ route('login.form') }}">Login</a><a href="{{ route('register.form') }}">Sign Up</a>@endauth
</nav></header>
<main class="{{ request()->routeIs('games.viewIndex') ? 'home-main' : '' }}">@if(session('success'))<p class="status-message" role="status">{{ session('success') }}</p>@endif
@if(session('error'))<p class="status-message" role="alert">{{ session('error') }}</p>@endif
@yield('content')</main>
<footer>Free browser games · <a href="{{ route('contacts.create') }}">Contact</a></footer>
@stack('scripts')
</body></html>
