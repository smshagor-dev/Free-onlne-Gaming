@extends('layouts.app')
@section('title', 'Admin sign in | FreeGames')
@section('content')
<section class="fg-auth-page" aria-labelledby="admin-login-title">
    <div class="fg-auth-layout">
        @include('auth.partials.aside', ['heading' => 'Manage FreeGames with confidence.', 'description' => 'Secure access for authorized team members. Use your administrator credentials to continue.'])
        <div class="fg-auth-panel">
            <span class="fg-auth-eyebrow">RESTRICTED ACCESS</span>
            <h1 id="admin-login-title">Admin sign in</h1>
            <p class="fg-auth-intro">Enter your authorized administrator account details.</p>
            @if(session('success'))<div class="fg-auth-alert is-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="fg-auth-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form class="fg-auth-form" method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="fg-auth-field">
                    <label for="admin-email">Admin email</label>
                    <input id="admin-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="admin@example.com" autofocus>
                    @error('email')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
                </div>
                <div class="fg-auth-field">
                    <label for="admin-password">Password</label>
                    <div class="fg-auth-password">
                        <input id="admin-password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        <button type="button" data-password-toggle="admin-password" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                </div>
                <div class="fg-auth-row">
                    <label class="fg-auth-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
                    <span class="fg-auth-help"><i class="fa-solid fa-lock" aria-hidden="true"></i> Authorized staff only</span>
                </div>
                <button class="fg-auth-submit" type="submit">Access dashboard <span aria-hidden="true">→</span></button>
            </form>
            <p class="fg-auth-switch"><a class="fg-auth-link" href="{{ route('games.viewIndex') }}">Return to FreeGames</a></p>
        </div>
    </div>
</section>
@endsection
