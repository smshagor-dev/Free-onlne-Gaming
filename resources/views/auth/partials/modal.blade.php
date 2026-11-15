@guest
<div class="fg-auth-modal" id="fg-auth-dialog" role="dialog" aria-modal="true" aria-labelledby="fg-auth-modal-title"
    data-auto-open="{{ in_array(old('auth_context'), ['login', 'register'], true) ? old('auth_context') : '' }}" hidden>
    <button class="fg-auth-backdrop" type="button" data-auth-close aria-label="Close authentication dialog"></button>
    <div class="fg-auth-modal-dialog" tabindex="-1">
        <button class="fg-auth-modal-close" type="button" data-auth-close aria-label="Close dialog">×</button>
        <span class="fg-auth-eyebrow">FREEGAMES ACCOUNT</span>
        <h2 id="fg-auth-modal-title">Sign in to FreeGames</h2>
        @if(old('auth_context') && ($errors->any() || session('error')))
            <div class="fg-auth-alert" role="alert">
                @if(session('error'))<p>{{ session('error') }}</p>@endif
                @if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
            </div>
        @endif
        <div class="fg-auth-modal-content" data-auth-panel="login">
            <p class="fg-auth-intro">Welcome back. Continue your free gaming journey.</p>
            @include('auth.partials.login-form', ['fieldPrefix' => 'modal-login', 'inModal' => true])
            @include('auth.partials.social')
            <p class="fg-auth-switch">New here? <a href="{{ route('register.form') }}" class="fg-auth-link" data-auth-open="register">Create an account</a></p>
        </div>
        <div class="fg-auth-modal-content" data-auth-panel="register" hidden>
            <p class="fg-auth-intro">Join FreeGames and save all your favorites.</p>
            @include('auth.partials.registration-form', ['fieldPrefix' => 'modal-register', 'inModal' => true])
            <p class="fg-auth-switch">Already have an account? <a href="{{ route('login.form') }}" class="fg-auth-link" data-auth-open="login">Sign in</a></p>
        </div>
    </div>
</div>
@endguest
