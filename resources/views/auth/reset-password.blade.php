@extends('layouts.app')
@section('title', 'Password recovery | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="legacy-reset-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure password recovery</span>
    <span class="fg-auth-eyebrow">PASSWORD RESET</span>
    <h1 id="legacy-reset-title">Use a secure reset link.</h1>
    <p>For your security, passwords can only be reset using the time-limited link sent to your registered email address.</p>
    <a class="fg-auth-submit" href="{{ route('auth.forgotPasswordForm') }}">Request a reset link <span aria-hidden="true">→</span></a>
</div></section>
@endsection
