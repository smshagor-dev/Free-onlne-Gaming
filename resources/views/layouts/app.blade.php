<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $pageTitle ?? ($setting->name ?? config('app.name', 'Free Games')))</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover and play free browser games. Save favorites and explore our lottery.' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="user-id" content="{{ auth()->id() }}">
    <meta name="robots" content="{{ $robots ?? ((auth()->check() || request()->is('user/*', 'admin/*')) ? 'noindex, nofollow' : 'index, follow') }}">
    <meta name="theme-color" content="#0b1220">
    <link rel="canonical" href="{{ url()->current() }}"><link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta property="og:title" content="{{ $pageTitle ?? config('app.name') }}"><meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Free browser games and giveaways.' }}">
    @if(!empty($ogImage) && filter_var($ogImage, FILTER_VALIDATE_URL) && in_array(parse_url($ogImage, PHP_URL_SCHEME), ['http', 'https'], true))<meta property="og:image" content="{{ $ogImage }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @if(!empty($structuredData))<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>@endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite('resources/js/app.js')
    @stack('head')
    @yield('styles')
    @include('layouts.theme')
</head>
<body class="fg-page">
<a class="skip-link" href="#main-content">Skip to content</a>
@include('layouts.header')
<main id="main-content" class="site-main {{ request()->routeIs('games.viewIndex') ? 'home-main' : '' }}">
    @yield('workspace-navigation')
    @if(auth()->check() && !request()->is('admin/*'))
        <nav class="account-nav" aria-label="Account navigation">
            <a href="{{ route('user.dashboard') }}">My Account</a><a href="{{ route('user.profile') }}">Profile</a>
            <a href="{{ route('user.games.viewFavorites') }}">Favorites</a><a href="{{ route('catalog.user.library') }}">My Library</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
        </nav>
    @endif
    @if(session('success'))<p class="status-message" role="status">{{ session('success') }}</p>@endif
    @if(session('error'))<p class="status-message" role="alert">{{ session('error') }}</p>@endif
    @yield('content')
</main>
<footer class="site-footer">Free browser games · <a href="{{ route('contacts.create') }}">Contact</a></footer>
@include('layouts.menu-script')
@stack('scripts')
</body>
</html>
