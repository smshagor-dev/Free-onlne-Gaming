@extends('layouts.app')
@section('title', 'Verify your email | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="verify-title">
    <div class="fg-auth-center">
        <span class="fg-auth-mark"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure account verification</span>
        <span class="fg-auth-eyebrow">ONE MORE STEP</span>
        <h1 id="verify-title">Verify your email</h1>
        <p>Enter the six-digit code we sent to <strong>{{ auth()->user()?->email ?? session('email') ?? 'your email address' }}</strong>.</p>
        @if(session('success'))<div class="fg-auth-alert is-success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="fg-auth-form" method="POST" action="{{ route('verify.code') }}">
            @csrf
            <div class="fg-auth-field">
                <label for="verification-code">Verification code</label>
                <input id="verification-code" class="fg-auth-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required pattern="[0-9]{6}" maxlength="6" placeholder="••••••" value="{{ old('code') }}" autofocus>
                @error('code')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
            </div>
            <button class="fg-auth-submit" type="submit">Verify account <span aria-hidden="true">→</span></button>
        </form>
        <div class="fg-auth-divider">Didn't receive the code?</div>
        <form class="fg-auth-form" method="POST" action="{{ route('verify.resend') }}" data-verification-resend>
            @csrf
            <input type="hidden" name="email" value="{{ auth()->user()?->email ?? session('email') }}">
            <button class="fg-auth-secondary" type="submit">Resend verification code</button>
            <p class="fg-auth-help" data-verification-message role="status" aria-live="polite" hidden></p>
        </form>
        <p class="fg-auth-switch"><a href="{{ route('login.form') }}" class="fg-auth-link">Back to sign in</a></p>
    </div>
</section>
@endsection
