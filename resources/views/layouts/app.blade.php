<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $pageTitle ?? ($setting->title ?? $setting->name ?? config('app.name', 'Free Games')))</title>
    <meta name="description" content="{{ $metaDescription ?? ($setting->seo_description ?? 'Discover and play free browser games. Save favorites and explore our lottery.') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="user-id" content="{{ auth()->id() }}">
    <meta name="robots" content="{{ $robots ?? ((auth()->check() || request()->is('user/*', 'admin/*')) ? 'noindex, nofollow' : 'index, follow') }}">
    <meta name="theme-color" content="#071827">
    <meta name="application-name" content="Free Games">
    <meta name="apple-mobile-web-app-title" content="Free Games">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @if(!empty($setting->favicon))
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->favicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/free-games-192.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/free-games-192.png') }}">
    <link rel="canonical" href="{{ url()->current() }}"><link rel="manifest" href="{{ asset('manifest.json') }}">
    @if(!empty($setting->meta_tag))<meta name="keywords" content="{{ $setting->meta_tag }}">@endif
    @php
        $cmsOgImage = !empty($setting->thumbnail_image)
            ? url(\Illuminate\Support\Facades\Storage::disk('public')->url($setting->thumbnail_image))
            : null;
        $effectiveOgImage = $ogImage ?? $cmsOgImage;
    @endphp
    <meta property="og:title" content="{{ $pageTitle ?? ($setting->seo_title ?? config('app.name')) }}"><meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $metaDescription ?? ($setting->seo_description ?? 'Free browser games and giveaways.') }}">
    @if(!empty($effectiveOgImage) && filter_var($effectiveOgImage, FILTER_VALIDATE_URL) && in_array(parse_url($effectiveOgImage, PHP_URL_SCHEME), ['http', 'https'], true))<meta property="og:image" content="{{ $effectiveOgImage }}">@endif
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
@include('auth.partials.modal')
@stack('scripts')
</body>
</html>
