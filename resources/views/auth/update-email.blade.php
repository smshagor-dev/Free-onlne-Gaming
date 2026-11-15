@extends('layouts.app')
@section('title', 'Update email | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="legacy-email-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Account email</span>
    <span class="fg-auth-eyebrow">SECURE EMAIL CHANGE</span>
    <h1 id="legacy-email-title">Manage your email from your profile.</h1>
    <p>To protect your account, email changes are handled in the authenticated profile settings.</p>
    @auth
        <a class="fg-auth-submit" href="{{ route('user.profile.edit') }}">Go to profile settings <span aria-hidden="true">→</span></a>
    @else
        <a class="fg-auth-submit" href="{{ route('login.form') }}">Sign in to continue <span aria-hidden="true">→</span></a>
    @endauth
</div></section>
@endsection
