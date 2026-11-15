@extends('layouts.app')
@section('title', 'Recover your account | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="forgot-title">
    <div class="fg-auth-center">
        <span class="fg-auth-mark"><i class="fa-solid fa-key" aria-hidden="true"></i> Account recovery</span>
        <span class="fg-auth-eyebrow">FORGOT PASSWORD?</span>
        <h1 id="forgot-title">Let's get you back in.</h1>
        <p>Enter your email address, username, or user ID. If we find an eligible account, we'll send a password reset link to its registered email.</p>
        @if(session('status'))<div class="fg-auth-alert is-success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('auth.searchUser') }}" class="fg-auth-form">
            @csrf
            <div class="fg-auth-field">
                <label for="recovery-identifier">Email, username, or user ID</label>
                <input id="recovery-identifier" name="identifier" type="text" value="{{ old('identifier') }}" placeholder="Your account identifier" required maxlength="255" autocomplete="username" autofocus>
                @error('identifier')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
            </div>
            <button class="fg-auth-submit" type="submit">Send reset link <span aria-hidden="true">→</span></button>
        </form>
        <p class="fg-auth-switch">Remembered your password? <a class="fg-auth-link" href="{{ route('login.form') }}">Sign in</a></p>
    </div>
</section>
@endsection
