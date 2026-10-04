@extends('layouts.app')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-vh-100 bg-[#0b141d] flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto">
        <!-- Success Card -->
        <div class="bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-700">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 py-6 text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-white mb-1">Registration Successful!</h2>
                <p class="text-blue-100">Your account has been created successfully</p>
            </div>

            <!-- Card Body -->
            <div class="p-6 bg-gray-800">
                <!-- Info Alert -->
                <div class="bg-blue-900 border border-blue-700 text-blue-100 rounded-lg p-3 mb-5 flex items-center">
                    <i class="fas fa-info-circle text-lg mr-2"></i>
                    <span class="text-sm">Please save your login credentials securely</span>
                </div>

                <!-- Credentials Card -->
                <div class="bg-gray-750 rounded-xl p-5 mb-5 border border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white">Your Login Details</h3>
                        <button class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-3 rounded-lg text-sm flex items-center transition-colors" onclick="copyToClipboard()">
                            <i class="fas fa-copy mr-1"></i> Copy
                        </button>
                    </div>
                    
                    <!-- Username Field -->
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-medium mb-2">Username</label>
                        <div class="flex flex-wrap sm:flex-nowrap">
                            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-600 bg-gray-700 text-white">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text"
                                   class="flex-1 min-w-0 bg-gray-700 border border-gray-600 text-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   id="username" value="{{ $username }}" readonly>
                            <button class="shrink-0 bg-gray-700 border border-gray-600 border-l-0 text-white px-4 hover:bg-gray-600 transition-colors"
                                    type="button" onclick="copyToClipboard('username')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-1">
                        <label class="block text-gray-400 text-sm font-medium mb-2">Password</label>
                        <div class="flex flex-wrap sm:flex-nowrap">
                            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-600 bg-gray-700 text-white">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password"
                                   class="flex-1 min-w-0 bg-gray-700 border border-gray-600 text-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   id="password" value="{{ $password }}" readonly>
                            <button class="shrink-0 bg-gray-700 border border-gray-600 border-l-0 text-white px-4 hover:bg-gray-600 transition-colors"
                                    type="button" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="shrink-0 bg-gray-700 border border-gray-600 border-l-0 text-white px-4 hover:bg-gray-600 transition-colors"
                                    type="button" onclick="copyToClipboard('password')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-3">
                    <button class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-3 px-4 rounded-xl font-medium flex items-center justify-center transition-all transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl" 
                            onclick="downloadCredentials()">
                        <i class="fas fa-download mr-2"></i> Download Login Details
                    </button>
                    <form method="POST" action="{{ route('login') }}" id="autoLoginForm">
                        @csrf
                        <input type="hidden" name="login" value="{{ $username }}">
                        <input type="hidden" name="password" value="{{ $password }}">

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                                text-white py-3 px-4 rounded-xl font-medium flex items-center justify-center
                                transition-all transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="mt-6 text-center text-gray-400 text-sm">
            <p>You can now access all features of our platform</p>
        </div>
    </div>
</div>

<script>
    // Show success message on page load
    $(document).ready(function() {
        Swal.fire({
            title: 'Registration Successful!',
            html: `<div class="text-center">
                     <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                         <i class="fas fa-check text-green-600 text-xl"></i>
                     </div>
                     <p class="mt-2">Your account has been created successfully</p>
                     <p class="text-sm text-gray-600 mt-1">Please save your credentials securely</p>
                   </div>`,
            icon: 'success',
            confirmButtonText: 'Continue',
            confirmButtonColor: '#16a34a',
            background: '#0b141d',
            color: '#fff',
            customClass: {
                popup: 'rounded-2xl border border-gray-700',
                title: 'text-lg font-semibold'
            }
        });
    });

    // Toggle password visibility
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const eyeIcon = document.querySelector('#password + button i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Copy credentials to clipboard
    function copyToClipboard() {
        const text = `Username: {{ $username }}\nPassword: {{ $password }}`;
        
        navigator.clipboard.writeText(text).then(() => {
            // Show success message
            const copyBtn = document.querySelector('button[onclick="copyToClipboard()"]');
            const originalHtml = copyBtn.innerHTML;
            
            copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
            copyBtn.classList.remove('bg-gray-700', 'hover:bg-gray-600');
            copyBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            setTimeout(() => {
                copyBtn.innerHTML = originalHtml;
                copyBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                copyBtn.classList.add('bg-gray-700', 'hover:bg-gray-600');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            Swal.fire({
                title: 'Error',
                text: 'Failed to copy to clipboard. Please try again.',
                icon: 'error',
                background: '#0b141d',
                color: '#fff',
                confirmButtonColor: '#16a34a',
                customClass: {
                    popup: 'rounded-2xl border border-gray-700'
                }
            });
        });
    }

    // Download credentials as text file
    function downloadCredentials() {
        const content = `=== Registration Successful ===

Your login credentials:

Username: {{ $username }}
Password: {{ $password }}

Important:
- Keep this information secure
- Do not share your credentials with anyone
- Change your password regularly for security

Generated on: ${new Date().toLocaleString()}

Thank you for joining us!`;
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = '{{ $username }}-credentials.txt';
        document.body.appendChild(a);
        a.click();
        
        // Clean up
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
        
        // Show download confirmation
        Swal.fire({
            title: 'Download Complete!',
            text: 'Your credentials have been downloaded as a text file.',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#16a34a',
            background: '#0b141d',
            color: '#fff',
            customClass: {
                popup: 'rounded-2xl border border-gray-700'
            }
        });
    }
</script>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const icon = event.currentTarget.querySelector("i");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function copyToClipboard(id) {
    const input = document.getElementById(id);
    input.select();
    input.setSelectionRange(0, 99999); // for mobile
    document.execCommand("copy");

    // Optional: show a quick copied alert
    alert(id.charAt(0).toUpperCase() + id.slice(1) + " copied!");
}
</script>

<style>
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #0b141d;
        color: #e2e8f0;
    }
    
    .bg-gray-750 {
        background-color: #252e39;
    }
    
    .bg-gray-800 {
        background-color: #1a202c;
    }
    
    .bg-blue-900 {
        background-color: #1e3a8a;
    }
    
    .border-blue-700 {
        border-color: #1d4ed8;
    }
    
    .text-blue-100 {
        color: #dbeafe;
    }
    
    .bg-gray-700 {
        background-color: #374151;
    }
    
    .bg-gray-600 {
        background-color: #4b5563;
    }
    
    .border-gray-600 {
        border-color: #4b5563;
    }
    
    .border-gray-700 {
        border-color: #374151;
    }
    
    input:read-only {
        cursor: not-allowed;
    }
    
    .shadow-lg {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
    }
    
    .shadow-xl {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
    }
    
    .rounded-2xl {
        border-radius: 1rem;
    }
</style>
@endsection