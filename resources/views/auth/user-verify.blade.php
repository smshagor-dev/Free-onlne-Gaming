@extends('layouts.app')
@section('title', 'Account verification | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="legacy-verify-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i> Email verification</span>
    <span class="fg-auth-eyebrow">VERIFY EMAIL</span>
    <h1 id="legacy-verify-title">Verify your account securely.</h1>
    <p>Your six-digit email code can be entered on our protected verification page.</p>
    @auth
        <a class="fg-auth-submit" href="{{ route('verify.form') }}">Enter verification code <span aria-hidden="true">→</span></a>
    @else
        <a class="fg-auth-submit" href="{{ route('login.form') }}">Sign in to continue <span aria-hidden="true">→</span></a>
    @endauth
</div></section>
@endsection
