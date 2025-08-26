@extends('layouts.app')

@section('content')
<div class="container mt-5 px-2">
    <style>
        body { background:#fafafa; }
        .auth-card { background:#fff; border:1px solid #eef2f7; border-radius:8px; padding:1.25rem; }
        .auth-title { font-size:1.25rem; font-weight:600; color:#111827; }
        .form-control { border-radius:6px; }
        .auth-footer a { color:#111827; text-decoration:none; opacity:0.8; }
    </style>
    <div class="row justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="auth-card">
                <div class="text-center mb-3">
                    <div class="auth-title"><i class="bi bi-person-circle me-1"></i>Login</div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label small">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 text-center auth-footer">
                    <a href="{{ route('register') }}">Belum punya akun? Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
