@extends('layouts.app')

@section('content')
<div class="container mt-5 px-2">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .auth-card {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(67,206,162,0.15);
            border-radius: 1.5rem;
        }
        .auth-title {
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff;
        }
        .form-control {
            border-radius: 0.75rem;
        }
        .btn-auth {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            color: white
        }
        .link-auth {
            color: #ffffff;
            font-weight: 500;
            text-decoration: none
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="auth-card p-4">
                <div class="auth-title mb-3 text-center"><i class="bi bi-person-circle me-2"></i>Login</div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first('email') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-auth w-100">Login</button>
                </form>
                <div class="mt-3 text-center ">
                    <a href="{{ route('register') }}" class="link-auth">Belum punya akun? Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
