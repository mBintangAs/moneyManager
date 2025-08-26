<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MoneyManager') }}</title>
    <link href="/bootstrap.min.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-light bg-white fixed-top" style="border-bottom:1px solid #e6e6e6;">
        <div class="container d-flex align-items-center py-2">
            <a class="navbar-brand text-dark me-2 mb-0" href="/">VibeMM</a>
            <button class="navbar-toggler ms-auto d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    @auth
                    <ul class="navbar-nav  pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('analytics') }}">Analytics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transactions.create') }}">Tambah Transaksi</a>
                        </li>
                     
                        <li class="nav-item mt-2">
                            <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="btn w-100 btn-outline-secondary btn-sm">Logout</button></form>
                        </li>
                    </ul>
                    @endauth
                </div>
            </div>
            @auth
                <div class="d-none d-lg-flex align-items-center gap-3 ms-auto">
                    <a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a>
                    <a href="{{ route('analytics') }}" class="text-muted text-decoration-none">Analytics</a>
                    <span class="small text-muted">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="mb-0">@csrf <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button></form>
                </div>
            @endauth
        </div>
    </nav>
    <main style="padding-top:70px;">
        @yield('content')
    </main>
    <script src="/jquery.min.js"></script>
    <script src="/bootstrap.bundle.min.js"></script>
    @yield('scripts')

</body>
</html>
