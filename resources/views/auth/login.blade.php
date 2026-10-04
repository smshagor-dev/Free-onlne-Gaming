<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
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
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
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

        .water-effect {
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
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-5%, 5%) rotate(1deg); }
            50% { transform: translate(-10%, 0) rotate(0deg); }
            75% { transform: translate(-5%, -5%) rotate(-1deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        .container-wrapper {
            position: relative;
            width: 100%;
            max-width: 450px;
            margin: 20px auto;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            width: 100%;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .premium-badge {
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

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: var(--primary);
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .login-header p {
            color: var(--gray);
            margin-top: 8px;
            font-size: 16px;
        }

        .logo-link {
            display: inline-block;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .logo-link:hover {
            transform: scale(1.05);
        }

        .site-logo {
            max-height: 80px;
            max-width: 200px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
            outline: none;
        }

        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
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
        
        .password-toggle:hover {
            color: var(--primary);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .remember-me label {
            color: var(--dark);
            font-weight: 500;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(78, 84, 200, 0.3);
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(78, 84, 200, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 15px;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
            background: rgba(231, 76, 60, 0.1);
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
            text-align: center;
        }

        .social-divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--gray);
            font-size: 14px;
        }
        
        .social-divider::before,
        .social-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--gray-light);
            margin: 0 10px;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .social-btn {
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

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .google-btn {
            background: #DB4437;
        }

        .facebook-btn {
            background: #4267B2;
        }

        .telegram-btn {
            background: #0088cc;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .login-container {
                padding: 30px 25px;
            }
            
            .login-header h2 {
                font-size: 28px;
            }
            
            .social-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 25px 20px;
                border-radius: 15px;
            }
            
            .site-logo {
                max-height: 60px;
                max-width: 160px;
            }
            
            .logo-link {
                margin-bottom: 15px;
            }
            
            .login-header h2 {
                font-size: 26px;
            }
            
            .login-header p {
                font-size: 14px;
            }
            
            .form-control {
                padding: 12px;
            }
            
            .premium-badge {
                right: 0;
                font-size: 10px;
                padding: 4px 10px;
            }
            
            .social-login {
                gap: 10px;
            }
            
            .social-btn {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 400px) {
            .login-header h2 {
                font-size: 24px;
            }
            
            .btn-login {
                padding: 14px;
                font-size: 15px;
            }
            
            .form-control {
                padding: 10px;
                font-size: 14px;
            }
        }

        /* Animation for form elements */
        .form-group {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Staggered animation for form elements */
        .form-group:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .form-group:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .remember-forgot {
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }
        
        .btn-login {
            animation: fadeInUp 0.5s ease-out 0.4s both;
        }
        
        .social-divider {
            animation: fadeInUp 0.5s ease-out 0.5s both;
        }
        
        .social-login {
            animation: fadeInUp 0.5s ease-out 0.6s both;
        }
        
        .login-footer {
            animation: fadeInUp 0.5s ease-out 0.7s both;
        }
    </style>
</head>
<body>
    <div class="water-effect"></div>
    
    <div class="container-wrapper">
        <div class="premium-badge">FREE GAMES</div>
        <div class="login-container">
            <div class="login-header">
                <a href="{{ url('/') }}" class="logo-link">
                    <img src="{{ asset('storage/settings/qs8K7pownxvUgrtH50Qrwgt3UaE37iSgojIygRK7.png') }}" 
                        alt="Company Logo" 
                        class="site-logo">
                </a>
                <h2>Welcome Back</h2>
                <p>Please login to your account</p>
            </div>

            @if(session('error'))
                <div class="error-message">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="login">Email, Username or UserID</label>
                    <input id="login" type="text" class="form-control" name="login" value="{{ old('login') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input id="password" type="password" class="form-control" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="{{route('auth.forgotPasswordForm')}}" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="social-divider">or Login with Social</div>

            <div class="social-login">
                <a href="{{ route('google.login') }}" class="social-btn google-btn" title="Login with Google">
                    <i class="fab fa-google"></i>
                </a>
                <a href="{{ route('facebook.login') }}" class="social-btn facebook-btn" title="Login with Facebook">
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

            <div class="login-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign up now</a></p>
            </div>
        </div>
    </div>

    <script>
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

        // Add focus effects to form inputs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Add animation class to form elements
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-group, .remember-forgot, .btn-login, .social-divider, .social-login, .login-footer').forEach(el => {
                el.classList.add('animate-in');
            });
        });
    </script>
    
</body>
</html>