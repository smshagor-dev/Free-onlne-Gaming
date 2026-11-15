@extends('layouts.app')
@section('title', 'Reset password | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="reset-link-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Account recovery</span>
    <span class="fg-auth-eyebrow">RESET PASSWORD</span>
    <h1 id="reset-link-title">Check your inbox.</h1>
    <p>Enter your account email address to receive a secure password reset link.</p>
    @if(session('status'))<div class="fg-auth-alert is-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="fg-auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="fg-auth-field"><label for="reset-link-email">Email address</label>
            <input id="reset-link-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" autofocus>
            @error('email')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <button class="fg-auth-submit" type="submit">Send reset link <span aria-hidden="true">→</span></button>
    </form>
    <p class="fg-auth-switch">Remember your password? <a class="fg-auth-link" href="{{ route('login.form') }}">Sign in</a></p>
</div></section>
@endsection
