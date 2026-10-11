@extends('layouts.app')
@section('title', 'Admin Login')
@push('head')




    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<style>
        .auth-page {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .auth-page .login-container {
            max-width: 400px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .auth-page .login-container h2 {
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .auth-page .form-control {
            border-radius: 8px;
            padding: 10px;
        }
        .auth-page .form-check-input {
            cursor: pointer;
        }
        .auth-page .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 8px;
            padding: 10px;
            transition: 0.3s;
            font-weight: bold;
        }
        .auth-page .btn-login:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
        }
        .auth-page .alert {
            font-size: 14px;
        }
        .auth-page .forgot-password {
            display: block;
            margin-top: 10px;
            color: #6b46c1;
            text-decoration: none;
        }
        .auth-page .forgot-password:hover {
            text-decoration: underline;
        }
    </style>
@endpush
@section('content')
<div class="auth-page">
    <div class="login-container">
        <h2>Admin Login</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <div class="mb-3 form-check text-start">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</div>
@endsection
