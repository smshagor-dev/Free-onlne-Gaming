@extends('layouts.app')
@section('title', 'Create account | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="register-title">
    <div class="fg-auth-layout">
        @include('auth.partials.aside', ['heading' => 'Get more out of every game.', 'description' => 'Create a free account to build your library, keep favorites, and find games you love.'])
        <div class="fg-auth-panel">
            <span class="fg-auth-eyebrow">JOIN FREEGAMES</span>
            <h1 id="register-title">Create your account</h1>
            <p class="fg-auth-intro">Just a few details to get you started.</p>
            @if($errors->any())
                <div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @include('auth.partials.registration-form', ['fieldPrefix' => 'page-register'])
            @include('auth.partials.social')
            <p class="fg-auth-switch">Already registered? <a class="fg-auth-link" href="{{ route('login.form') }}">Sign in</a></p>
        </div>
    </div>
</section>
@endsection
