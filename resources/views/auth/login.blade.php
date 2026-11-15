@extends('layouts.app')
@section('title', 'Sign in | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="login-title">
    <div class="fg-auth-layout">
        @include('auth.partials.aside', ['heading' => 'Welcome back to your game space.', 'description' => 'Sign in to pick up where you left off and discover your next favorite game.'])
        <div class="fg-auth-panel">
            <span class="fg-auth-eyebrow">WELCOME BACK</span>
            <h1 id="login-title">Sign in to FreeGames</h1>
            <p class="fg-auth-intro">Your games, favorites, and account in one place.</p>
            @if(session('error'))
                <p class="fg-auth-alert" role="alert">{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @include('auth.partials.login-form', ['fieldPrefix' => 'page-login'])
            @include('auth.partials.social')
            <p class="fg-auth-switch">New to FreeGames? <a class="fg-auth-link" href="{{ route('register.form') }}">Create an account</a></p>
        </div>
    </div>
</section>
@endsection
