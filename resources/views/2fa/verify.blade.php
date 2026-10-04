<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            primary: '#0b141d',
                            card: '#1a2634',
                            input: '#243142',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b141d;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="min-h-screen bg-dark-primary flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Two-Factor Authentication
        </h2>
        <p class="mt-2 text-center text-sm text-gray-300">
            Please enter the code from your authenticator app
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-dark-card py-8 px-4 shadow-2xl sm:rounded-lg sm:px-10 border border-gray-700">
            
            {{-- Success / Error Messages --}}
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-500 text-white p-3 rounded mb-4">
                    {{ $errors->first('otp') }}
                </div>
            @endif

            {{-- OTP Form --}}
            <form method="POST" action="{{ route('2fa.verify') }}">
                @csrf
                <div class="mb-4">
                    <label for="otp" class="block text-sm font-medium text-gray-300">
                        Verification Code
                    </label>
                    <div class="mt-1">
                        <input id="otp" name="otp" type="text" inputmode="numeric" 
                               pattern="[0-9]*" autocomplete="one-time-code" required 
                               class="input-field appearance-none block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-dark-input text-white">
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Verify
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
