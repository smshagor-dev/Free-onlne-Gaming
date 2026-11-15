@php
    $fieldPrefix = $fieldPrefix ?? 'signup';
    $inModal = $inModal ?? false;
    $referral = old('referrer', request()->query('ref', ''));
@endphp
<form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="fg-auth-form">
    @csrf
    @if($inModal)<input type="hidden" name="auth_context" value="register">@endif
    <div class="fg-auth-fields">
        <div class="fg-auth-field">
            <label for="{{ $fieldPrefix }}-name">Full name</label>
            <input id="{{ $fieldPrefix }}-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Your full name" @if(!$inModal) autofocus @endif>
            @error('name')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field">
            <label for="{{ $fieldPrefix }}-username">Username</label>
            <input id="{{ $fieldPrefix }}-username" type="text" name="username" value="{{ old('username') }}" required maxlength="50" autocomplete="username" placeholder="Choose a username">
            @error('username')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field is-wide">
            <label for="{{ $fieldPrefix }}-email">Email address</label>
            <input id="{{ $fieldPrefix }}-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="you@example.com">
            @error('email')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field">
            <label for="{{ $fieldPrefix }}-password">Password</label>
            <div class="fg-auth-password">
                <input id="{{ $fieldPrefix }}-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters" data-strength-password>
                <button type="button" data-password-toggle="{{ $fieldPrefix }}-password" aria-label="Show password" aria-pressed="false">Show</button>
            </div>
            @error('password')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="fg-auth-field">
            <label for="{{ $fieldPrefix }}-password-confirm">Confirm password</label>
            <div class="fg-auth-password">
                <input id="{{ $fieldPrefix }}-password-confirm" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repeat your password">
                <button type="button" data-password-toggle="{{ $fieldPrefix }}-password-confirm" aria-label="Show confirmation password" aria-pressed="false">Show</button>
            </div>
        </div>
        <div class="fg-auth-field is-wide">
            <div class="fg-auth-strength" data-password-strength aria-live="polite">Use 8 or more characters with a mix of letters, numbers and symbols.
                <meter min="0" max="4" value="0" aria-label="Password strength"></meter>
            </div>
        </div>
    </div>
    <details class="fg-auth-details" @if(old('country') || old('currency') || old('mobile_number') || $referral) open @endif>
        <summary>More details (optional)</summary>
        <div class="fg-auth-fields">
            <div class="fg-auth-field">
                <label for="{{ $fieldPrefix }}-country">Country</label>
                <select id="{{ $fieldPrefix }}-country" name="country" data-country-select data-selected="{{ old('country') }}" data-source="{{ url('/countries') }}">
                    <option value="">Select country</option>
                </select>
            </div>
            <div class="fg-auth-field">
                <label for="{{ $fieldPrefix }}-currency">Currency</label>
                <select id="{{ $fieldPrefix }}-currency" name="currency" data-currency-select data-selected="{{ old('currency') }}">
                    <option value="">Select currency</option>
                </select>
            </div>
            <div class="fg-auth-field">
                <label for="{{ $fieldPrefix }}-mobile">Phone number</label>
                <input id="{{ $fieldPrefix }}-mobile" type="tel" name="mobile_number" maxlength="20" autocomplete="tel" value="{{ old('mobile_number') }}" placeholder="Optional">
            </div>
            <div class="fg-auth-field">
                <label for="{{ $fieldPrefix }}-referrer">Referral code</label>
                <input id="{{ $fieldPrefix }}-referrer" name="referrer" type="text" maxlength="50" value="{{ $referral }}" placeholder="Optional">
            </div>
            <div class="fg-auth-field is-wide">
                <label for="{{ $fieldPrefix }}-photo">Profile photo (optional, max 2 MB)</label>
                <input id="{{ $fieldPrefix }}-photo" type="file" name="photo" accept="image/jpeg,image/png,image/gif">
                @error('photo')<span class="fg-auth-error" role="alert">{{ $message }}</span>@enderror
            </div>
        </div>
    </details>
    <button class="fg-auth-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
    <p class="fg-auth-help">We'll send a verification code to your email address.</p>
</form>
