@extends('layouts.app')
@section('title', 'Choose new password | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="reset-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure password reset</span>
    <span class="fg-auth-eyebrow">SET NEW PASSWORD</span>
    <h1 id="reset-title">Create a new password.</h1>
    <p>Choose a strong password to protect your FreeGames account.</p>
    @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="fg-auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="fg-auth-field"><label for="reset-email">Email address</label>
            <input id="reset-email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
            @error('email')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field"><label for="reset-new-password">New password</label>
            <div class="fg-auth-password">
                <input id="reset-new-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters" data-strength-password>
                <button type="button" data-password-toggle="reset-new-password" aria-label="Show password" aria-pressed="false">Show</button>
            </div>
            <div class="fg-auth-strength" data-password-strength aria-live="polite">Use 8 or more characters.<meter min="0" max="4" value="0" aria-label="Password strength"></meter></div>
            @error('password')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field"><label for="reset-password-confirm">Confirm password</label>
            <div class="fg-auth-password">
                <input id="reset-password-confirm" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repeat your password">
                <button type="button" data-password-toggle="reset-password-confirm" aria-label="Show confirmation password" aria-pressed="false">Show</button>
            </div>
        </div>
        <button class="fg-auth-submit" type="submit">Update password <span aria-hidden="true">→</span></button>
    </form>
</div></section>
@endsection
