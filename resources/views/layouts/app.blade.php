<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        // Page-specific titles take precedence. Otherwise use the SEO title
        // from the settings table, then the existing site identity.
        $siteSeoTitle = filled($setting->seo_title ?? null)
            ? $setting->seo_title
            : ($setting?->title ?: ($setting?->name ?: config('app.name', 'Free Games')));
        $siteSeoDescription = filled($setting->seo_description ?? null)
            ? $setting->seo_description
            : 'Discover and play free browser games online.';
    @endphp
    <title>@yield('title', $pageTitle ?? $siteSeoTitle)</title>
    <meta name="description" content="{{ $metaDescription ?? $siteSeoDescription }}">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="user-id" content="{{ auth()->id() }}">
    <meta name="robots" content="{{ $robots ?? ((auth()->check() || request()->is('user/*', 'admin/*')) ? 'noindex, nofollow' : 'index, follow') }}">
    @php($pwaConfig = \App\Services\SiteCms::section('pwa'))
    <meta name="theme-color" content="{{ $pwaConfig['theme_color'] }}">
    <meta name="application-name" content="{{ $pwaConfig['name'] }}">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaConfig['short_name'] }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @if(!empty($setting->favicon))
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->favicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/free-games-192.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/free-games-192.png') }}">
    <link rel="canonical" href="{{ url()->current() }}"><link rel="manifest" href="{{ route('site.manifest') }}">
    @if(!empty($setting->meta_tag))<meta name="keywords" content="{{ $setting->meta_tag }}">@endif
    @php
        $cmsOgImage = !empty($setting->thumbnail_image)
            ? url(\Illuminate\Support\Facades\Storage::disk('public')->url($setting->thumbnail_image))
            : null;
        $effectiveOgImage = $ogImage ?? $cmsOgImage;
    @endphp
    <meta property="og:title" content="{{ $pageTitle ?? $siteSeoTitle }}"><meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $metaDescription ?? $siteSeoDescription }}">
    @if(!empty($effectiveOgImage) && filter_var($effectiveOgImage, FILTER_VALIDATE_URL) && in_array(parse_url($effectiveOgImage, PHP_URL_SCHEME), ['http', 'https'], true))<meta property="og:image" content="{{ $effectiveOgImage }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @if(!empty($structuredData))<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>@endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @php($userPortal = auth()->check() && (request()->is('user/*') || request()->routeIs('catalog.user.*', '2fa.setup')))
    @vite('resources/js/app.js')
    @if($userPortal)
        @vite('resources/js/admin-cms.js')
    @endif
    @stack('head')
    @yield('styles')
    @include('layouts.theme')
    @if($userPortal)
        @include('layouts.partials.player-portal-style')
    @endif
</head>
<body class="fg-page {{ request()->routeIs('user.dashboard') ? 'player-dashboard-route' : '' }} {{ $userPortal ? 'cms-body user-portal' : '' }}">
@if($userPortal)
    @include('layouts.partials.player-portal')
@else
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('layouts.header')
    <main id="main-content" class="site-main {{ request()->routeIs('games.viewIndex') ? 'home-main' : '' }}">
        @yield('workspace-navigation')
        @if(session('success'))<p class="status-message" role="status">{{ session('success') }}</p>@endif
        @if(session('error'))<p class="status-message" role="alert">{{ session('error') }}</p>@endif
        @yield('content')
    </main>
    @php($cmsFooter = \App\Services\SiteCms::section('footer'))
    <footer class="site-footer">
        {{ $cmsFooter['text'] }}
        @if($cmsFooter['show_games']) · <a href="{{ route('playable.index') }}">Games</a> @endif
        @if($cmsFooter['show_contact']) · <a href="{{ route('contacts.create') }}">{{ $cmsFooter['contact_label'] }}</a> @endif
    </footer>
@endif
@include('layouts.menu-script')
@include('auth.partials.modal')
@stack('scripts')
</body>
</html>
