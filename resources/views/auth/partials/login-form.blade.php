@php
    $fieldPrefix = $fieldPrefix ?? 'auth';
    $inModal = $inModal ?? false;
@endphp
<form method="POST" action="{{ route('login') }}" class="fg-auth-form">
    @csrf
    @if($inModal)<input type="hidden" name="auth_context" value="login">@endif
    <div class="fg-auth-field">
        <label for="{{ $fieldPrefix }}-login">Email, username or user ID</label>
        <input id="{{ $fieldPrefix }}-login" name="login" type="text" required maxlength="255" autocomplete="username"
            placeholder="Enter your email or username" value="{{ old('login') }}" @if(!$inModal) autofocus @endif>
        @error('login')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
    </div>
    <div class="fg-auth-field">
        <label for="{{ $fieldPrefix }}-password">Password</label>
        <div class="fg-auth-password">
            <input id="{{ $fieldPrefix }}-password" name="password" type="password" required autocomplete="current-password"
                placeholder="Enter your password">
            <button type="button" data-password-toggle="{{ $fieldPrefix }}-password" aria-label="Show password" aria-pressed="false">Show</button>
        </div>
        @error('password')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
    </div>
    <div class="fg-auth-row">
        <label class="fg-auth-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
        <a class="fg-auth-link" href="{{ route('auth.forgotPasswordForm') }}">Forgot password?</a>
    </div>
    <button class="fg-auth-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
</form>
