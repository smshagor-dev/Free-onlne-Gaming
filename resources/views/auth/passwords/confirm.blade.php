@extends('layouts.app')
@section('title', 'Confirm password | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="confirm-title"><div class="fg-auth-center">
    <span class="fg-auth-mark"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Security check</span>
    <span class="fg-auth-eyebrow">CONFIRM IT'S YOU</span>
    <h1 id="confirm-title">Confirm your password.</h1>
    <p>For your security, please enter your current password before continuing.</p>
    @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="fg-auth-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="fg-auth-field"><label for="confirm-current-password">Current password</label>
            <div class="fg-auth-password">
                <input id="confirm-current-password" type="password" name="password" required autocomplete="current-password">
                <button type="button" data-password-toggle="confirm-current-password" aria-label="Show password" aria-pressed="false">Show</button>
            </div>
            @error('password')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <button class="fg-auth-submit" type="submit">Confirm password <span aria-hidden="true">→</span></button>
    </form>
    <p class="fg-auth-switch"><a class="fg-auth-link" href="{{ route('auth.forgotPasswordForm') }}">Forgot your password?</a></p>
</div></section>
@endsection
