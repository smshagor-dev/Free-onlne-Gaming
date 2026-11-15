@extends('layouts.app')
@section('title', 'Account created | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="quick-success-title">
    <div class="fg-auth-center">
        <span class="fg-auth-mark"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Account created</span>
        <span class="fg-auth-eyebrow">WELCOME TO FREEGAMES</span>
        <h1 id="quick-success-title">Your account is ready.</h1>
        <p>Save your temporary sign-in details below. This generated password is shown only on this page.</p>
        <div class="fg-auth-alert" role="status">Important: This quick account uses generated credentials. Save them securely before leaving this screen.</div>
        <div class="fg-auth-form" style="margin-top:20px">
            <div class="fg-auth-field">
                <label for="quick-success-user">Username</label>
                <div class="fg-auth-password">
                    <input id="quick-success-user" type="text" readonly value="{{ $username }}">
                    <button type="button" data-copy-target="quick-success-user" aria-label="Copy username">Copy</button>
                </div>
            </div>
            <div class="fg-auth-field">
                <label for="quick-success-password">Generated password</label>
                <div class="fg-auth-password">
                    <input id="quick-success-password" type="password" readonly value="{{ $password }}">
                    <button type="button" data-password-toggle="quick-success-password" aria-label="Show generated password" aria-pressed="false">Show</button>
                </div>
                <button type="button" class="fg-auth-secondary" data-copy-target="quick-success-password">Copy password</button>
            </div>
            <a class="fg-auth-submit" href="{{ route('login.form') }}">Continue to sign in <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>
@endsection
