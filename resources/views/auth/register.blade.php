@extends('layouts.app')
@section('title', 'Registration')
@push('head')




    <!-- Font Awesome for eye icons -->

    <!-- Select2 for enhanced dropdowns -->


<style>
        .auth-page {
            --primary: #4e54c8;
            --primary-dark: #3f43a9;
            --secondary: #ff6b6b;
            --accent: #ffd93d;
            --light: #f7f9fc;
            --dark: #2d3436;
            --success: #2ecc71;
            --success-dark: #27ae60;
            --gray: #7f8c8d;
            --gray-light: #dfe6e9;
        }

        .auth-page * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .auth-page {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            color: var(--dark);
            padding: 20px;
        }

        .auth-page .water-effect {
            position: fixed;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover;
            animation: waterMovement 20s infinite linear;
            opacity: 0.1;
            z-index: 0;
        }

        @keyframes waterMovement {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(-5%, 5%) rotate(1deg);
            }

            50% {
                transform: translate(-10%, 0) rotate(0deg);
            }

            75% {
                transform: translate(-5%, -5%) rotate(-1deg);
            }

            100% {
                transform: translate(0, 0) rotate(0deg);
            }
        }

        .auth-page .container-wrapper {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }

        .auth-page .register-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            width: 100%;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .auth-page .premium-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transform: rotate(5deg);
            z-index: 2;
        }

        .auth-page .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-page .register-header h2 {
            color: var(--primary);
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .auth-page .register-header p {
            color: var(--gray);
            margin-top: 8px;
            font-size: 16px;
        }

        .auth-page .logo-link {
            display: inline-block;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .auth-page .logo-link:hover {
            transform: scale(1.05);
        }

        .auth-page .site-logo {
            max-height: 80px;
            max-width: 200px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .auth-page .tabs {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--gray-light);
        }

        .auth-page .tab {
            padding: 12px 24px;
            cursor: pointer;
            font-weight: 600;
            color: var(--gray);
            transition: all 0.3s;
            text-align: center;
            flex: 1;
            position: relative;
        }

        .auth-page .tab.active {
            color: var(--primary);
        }

        .auth-page .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        .auth-page .tab-content {
            display: none;
        }

        .auth-page .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-page .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .auth-page .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }

        .auth-page .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
        }

        .auth-page .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
            outline: none;
        }

        .auth-page .select2-container--default .select2-selection--single {
            height: 50px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            padding: 10px;
        }

        .auth-page .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary);
            outline: none;
        }

        .auth-page .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            padding-left: 0;
        }

        .auth-page .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px;
        }

        .auth-page .photo-upload {
            display: flex;
            align-items: center;
        }

        .auth-page .photo-upload input[type="file"] {
            display: none;
        }

        .auth-page .photo-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #f1f1f1;
            margin-right: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ccc;
        }

        .auth-page .photo-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }

        .auth-page .upload-btn {
            padding: 12px 18px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .auth-page .upload-btn:hover {
            background: var(--primary-dark);
        }

        .auth-page .btn-register {
            width: 100%;
            padding: 16px;
            background: var(--success);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);
        }

        .auth-page .btn-register:hover {
            background: var(--success-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(46, 204, 113, 0.4);
        }

        .auth-page .register-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 15px;
        }

        .auth-page .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-page .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
            background: rgba(231, 76, 60, 0.1);
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
        }

        .auth-page .error-message ul {
            margin: 0;
            padding-left: 20px;
        }

        .auth-page .password-wrapper {
            position: relative;
        }

        .auth-page .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
            background: none;
            border: none;
            padding: 5px;
        }

        .auth-page .social-divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--gray);
            font-size: 14px;
        }

        .auth-page .social-divider::before,
        .auth-page .social-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--gray-light);
            margin: 0 10px;
        }

        .auth-page .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .auth-page .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .auth-page .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .auth-page .google-btn {
            background: #DB4437;
        }

        .auth-page .facebook-btn {
            background: #4267B2;
        }

        .auth-page .telegram-btn {
            background: #0088cc;
        }

        .auth-page .password-toggle:hover {
            color: var(--primary);
        }

        .auth-page .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .auth-page .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .auth-page .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .auth-page .select2-results__option[aria-selected="true"] {
            background-color: #f0f4ff !important;
        }

        .auth-page .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary) !important;
        }

        /* Currency symbol styling */
        .auth-page .currency-symbol {
            font-weight: bold;
            margin-right: 5px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .auth-page .form-row {
                flex-direction: column;
                gap: 20px;
            }

            .auth-page .register-container {
                padding: 25px 20px;
            }

            .auth-page .tab {
                padding: 10px 15px;
                font-size: 14px;
            }

            .auth-page .register-header h2 {
                font-size: 28px;
            }

            .auth-page .social-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }

            .auth-page .photo-upload {
                flex-direction: column;
                align-items: flex-start;
            }

            .auth-page .photo-preview {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 576px) {
            .auth-page {
                padding: 10px;
            }

            .auth-page .register-container {
                padding: 20px 15px;
                border-radius: 15px;
            }

            .auth-page .site-logo {
                max-height: 60px;
                max-width: 160px;
            }

            .auth-page .logo-link {
                margin-bottom: 15px;
            }

            .auth-page .register-header h2 {
                font-size: 24px;
            }

            .auth-page .register-header p {
                font-size: 14px;
            }

            .auth-page .tabs {
                flex-direction: column;
            }

            .auth-page .tab {
                padding: 10px;
            }

            .auth-page .form-control,
            .auth-page .select2-container--default .select2-selection--single {
                padding: 12px;
            }

            .auth-page .premium-badge {
                right: 0;
                font-size: 10px;
                padding: 4px 10px;
            }

            .auth-page .social-login {
                gap: 10px;
            }

            .auth-page .social-btn {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }

        @media (max-width: 400px) {
            .auth-page .register-header h2 {
                font-size: 22px;
            }

            .auth-page .btn-register {
                padding: 14px;
                font-size: 15px;
            }

            .auth-page .form-control,
            .auth-page .select2-container--default .select2-selection--single {
                padding: 10px;
                font-size: 14px;
            }
        }

        /* Select2 mobile optimization */
        .auth-page .select2-container--open .select2-dropdown--below {
            border-radius: 0 0 10px 10px;
        }

        .auth-page .select2-results__option {
            padding: 10px 12px;
        }

        @media (max-width: 576px) {
            .auth-page .select2-container--default .select2-selection--single {
                height: 45px;
            }

            .auth-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 43px;
            }
        }
    </style>
@endpush
@section('content')
<div class="auth-page">
    <div class="water-effect"></div>

    <div class="container-wrapper">
        <div class="premium-badge">FREE GAME</div>
        <div class="register-container">
            <div class="register-header">
                <h2>Create Your Account</h2>
                <p>Join our exclusive community today</p>
            </div>

            @if ($errors->any())
            <div class="error-message">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="social-login">
                <a href="{{ route('google.login') }}" class="social-btn google-btn" title="Sign up with Google">
                    <i class="fab fa-google"></i>
                </a>
                <a href="{{ route('facebook.login') }}" class="social-btn facebook-btn" title="Sign up with Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <div id="telegram-login"></div>
                <script async src="https://telegram.org/js/telegram-widget.js?22"
                        data-telegram-login="freegameauth_bot"
                        data-size="large"
                        data-auth-url="{{ route('telegram.callback') }}"
                        data-request-access="write">
                </script>
            </div>

            <div class="social-divider">or register with email</div>

            <div class="tabs">
                <div class="tab active" data-tab="quick">Quick Registration</div>
                <div class="tab" data-tab="full">Full Registration</div>
            </div>

            <!-- Quick Registration Form -->
            <div id="quick-tab" class="tab-content active">
                <form method="POST" action="{{ route('quick.register') }}" id="quick-register-form">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quick-country">Country</label>
                            <select id="quick-country" class="form-control country-select" name="country">
                                <option value="">Select Country</option>
                                <!-- Countries will be loaded via AJAX -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quick-currency">Currency</label>
                            <select id="quick-currency" class="form-control currency-select" name="currency">
                                <option value="">Select Currency</option>
                                <!-- Currencies will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="referrer-quick">Referral Code (Optional)</label>
                        <input id="referrer-quick" type="text" class="form-control" name="referrer" value="{{ old('referrer') }}">
                    </div>

                    <button type="submit" class="btn-register">Quick Register</button>
                </form>
            </div>

            <!-- Full Registration Form -->
            <div id="full-tab" class="tab-content">
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="full-register-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" class="form-control country-select" name="country">
                                <option value="">Select Country</option>
                                <!-- Countries will be loaded via AJAX -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <select id="currency" class="form-control currency-select" name="currency">
                                <option value="">Select Currency</option>
                                <!-- Currencies will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="mobile_number">Mobile Number *</label>
                        <input id="mobile_number" type="text" class="form-control" name="mobile_number" value="{{ old('mobile_number') }}">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-wrapper">
                            <input id="password" type="password" class="form-control" name="password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password-confirm">Confirm Password *</label>
                        <div class="password-wrapper">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password-confirm')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="referrer-full">Referral Code (Optional)</label>
                        <input id="referrer-full" type="text" class="form-control" name="referrer" value="{{ old('referrer') }}">
                    </div>

                    <div class="form-group">
                        <label>Profile Photo (Optional)</label>
                        <div class="photo-upload">
                            <div class="photo-preview">
                                <img id="photo-preview-img" src="#" alt="Preview">
                                <span id="photo-placeholder">No photo</span>
                            </div>
                            <label for="photo" class="upload-btn">Choose Photo</label>
                            <input id="photo" type="file" name="photo" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn-register">Complete Registration</button>
                </form>
            </div>

            <div class="register-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- jQuery and Select2 JS -->
    <script>
        fetch('/countries')
            .then(response => { if (!response.ok) throw new Error('Countries unavailable'); return response.json(); })
            .then(countries => {
                document.querySelectorAll('.country-select').forEach(select => {
                    const form = select.closest('form');
                    const currency = form.querySelector('.currency-select');
                    const mobile = form.querySelector('[name="mobile_number"]');
                    select.replaceChildren(new Option('Select Country', ''));
                    currency.replaceChildren(new Option('Select Currency', ''));
                    countries.forEach(country => {
                        select.add(new Option(country.name, country.name));
                        if (![...currency.options].some(option => option.value === country.currency)) currency.add(new Option(country.currency, country.currency));
                    });
                    select.addEventListener('change', () => {
                        const country = countries.find(country => country.name === select.value);
                        currency.value = country?.currency ?? '';
                        if (mobile && country) mobile.value = '+' + country.dial + ' ';
                    });
                });
            }).catch(() => {});

        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                tab.classList.add('active');

                const tabId = tab.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleIcon = field.parentElement.querySelector('i');

            if (field.type === "password") {
                field.type = "text";
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = "password";
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Photo preview functionality
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const placeholder = document.getElementById('photo-placeholder');
            const previewImg = document.getElementById('photo-preview-img');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    previewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        // Form validation
        document.getElementById('quick-register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('quick-password').value;
            const confirmPassword = document.getElementById('quick-password-confirm').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        document.getElementById('full-register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password-confirm').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // Mobile menu toggle for small screens
        function setupMobileMenu() {
            if (window.innerWidth <= 576) {
                // Add mobile menu toggle if needed
            }
        }

        // Initialize on load and resize
        window.addEventListener('load', setupMobileMenu);
        window.addEventListener('resize', setupMobileMenu);

        const urlParams = new URLSearchParams(window.location.search);
        const refCode = urlParams.get('ref');

        if (refCode) {
            const quickInput = document.getElementById('referrer-quick');
            const fullInput = document.getElementById('referrer-full');

            if (quickInput) quickInput.value = refCode;
            if (fullInput) fullInput.value = refCode;
        }
    </script>
</div>
@endsection
