<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @keyframes waterMovement {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-5%, 5%) rotate(1deg); }
            50% { transform: translate(-10%, 0) rotate(0deg); }
            75% { transform: translate(-5%, -5%) rotate(-1deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
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
        
        .email-popup {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .email-popup.show {
            max-height: 100px;
            transition: max-height 0.3s ease-in;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="water-effect"></div>
    
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative z-10">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verify Your Account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                We've sent a verification code to your email
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 backdrop-blur-sm bg-white/90">
                <!-- Status Messages -->
                <div id="status-message" class="hidden mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    <!-- Dynamic content will be inserted here -->
                </div>
                
                <div id="error-message" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <!-- Dynamic content will be inserted here -->
                </div>

                <!-- Verification Form -->
                <form class="space-y-6" action="{{ route('verify.code') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" id="user-email" value="{{ $email ?? '' }}">
                    
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">
                            Verification Code
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input id="code" name="code" type="number" required
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="Enter 6-digit code"
                                   maxlength="6"
                                   pattern="\d{6}">
                        </div>
                        <p id="code-error" class="hidden mt-2 text-sm text-red-600"></p>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Verify Account
                        </button>
                    </div>
                </form>

                <!-- Resend Code Section -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">
                                Didn't receive code?
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <!-- Email input (hidden by default) -->
                        <div id="email-popup" class="email-popup">
                            <label for="resend-email" class="block text-sm font-medium text-gray-700">
                                Enter your email
                            </label>
                            <input type="email" id="resend-email" name="email"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="your@email.com">
                            <p id="email-error" class="hidden mt-2 text-sm text-red-600"></p>
                            
                            <button id="submit-resend"
                                    class="w-full mt-2 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                Submit
                            </button>
                        </div>
                        
                        <button id="resend-button"
                                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Resend Verification Code
                        </button>
                        <p id="resend-timer" class="text-center text-xs text-gray-500 mt-2 hidden">
                            Resend available in <span id="countdown">60</span> seconds
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const resendButton = document.getElementById('resend-button');
            const submitResend = document.getElementById('submit-resend');
            const emailPopup = document.getElementById('email-popup');
            const emailInput = document.getElementById('resend-email');
            const userEmail = document.getElementById('user-email').value;

            // Show email popup when resend button is clicked
            resendButton.addEventListener('click', function() {
                // If email is already known, skip the popup and resend immediately
                if (userEmail) {
                    resendVerificationCode(userEmail);
                    return;
                }
                
                // Show the email popup
                emailPopup.classList.add('show');
                resendButton.classList.add('hidden');
            });

            // Handle the submit button in the popup
            submitResend.addEventListener('click', function() {
                const email = emailInput.value;
                const emailError = document.getElementById('email-error');
                
                // Validate email
                if (!email) {
                    emailError.textContent = 'Please enter your email address';
                    emailError.classList.remove('hidden');
                    return;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    emailError.textContent = 'Please enter a valid email address';
                    emailError.classList.remove('hidden');
                    return;
                }
                
                emailError.classList.add('hidden');
                resendVerificationCode(email);
            });

            function resendVerificationCode(email) {
                // Disable button and show loading state
                submitResend.disabled = true;
                submitResend.innerHTML = 'Sending...';
                
                // Create form data for POST
                const formData = new FormData();
                formData.append('email', email);
                formData.append('_token', csrfToken);
                
                // Make AJAX request
                fetch("{{ route('verify.resend') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(async response => {
                    // First check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    }
                    return { success: false, message: 'Unexpected response from server' };
                })
                .then(data => {
                    if (data.success) {
                        showMessage(data.message || 'New verification code sent!', 'status');
                        startCountdown(60); // Start 60-second countdown
                        
                        // Hide the popup and show the resend button again
                        emailPopup.classList.remove('show');
                        resendButton.classList.remove('hidden');
                        
                        // Store the email in the hidden field
                        document.getElementById('user-email').value = email;
                    } else {
                        showMessage(data.message || 'Failed to resend code', 'error');
                        if (data.errors && data.errors.email) {
                            document.getElementById('email-error').textContent = data.errors.email[0];
                            document.getElementById('email-error').classList.remove('hidden');
                        }
                    }
                })
                .catch(error => {
                    showMessage('An error occurred while processing your request', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitResend.disabled = false;
                    submitResend.innerHTML = 'Submit';
                });
            }

            // Helper function to show messages
            function showMessage(message, type) {
                const elementId = type === 'status' ? 'status-message' : 'error-message';
                const element = document.getElementById(elementId);
                element.textContent = message;
                element.classList.remove('hidden');
                
                // Hide after 5 seconds
                setTimeout(() => {
                    element.classList.add('hidden');
                }, 5000);
            }

            // Countdown timer for resend button
            function startCountdown(seconds) {
                const timerElement = document.getElementById('resend-timer');
                const countdownElement = document.getElementById('countdown');
                
                resendButton.disabled = true;
                timerElement.classList.remove('hidden');
                
                let remaining = seconds;
                countdownElement.textContent = remaining;
                
                const interval = setInterval(() => {
                    remaining--;
                    countdownElement.textContent = remaining;
                    
                    if (remaining <= 0) {
                        clearInterval(interval);
                        resendButton.disabled = false;
                        timerElement.classList.add('hidden');
                    }
                }, 1000);
            }

            // Input validation
            document.getElementById('code').addEventListener('input', function() {
                if (this.value.length > 6) {
                    this.value = this.value.slice(0, 6);
                }
                
                if (this.value.length === 6) {
                    document.getElementById('code-error').classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>