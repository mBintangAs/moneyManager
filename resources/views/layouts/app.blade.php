<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MoneyManager') }}</title>
    <link href="/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#43cea2">
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/"><span class="fw-bold text-primary">VibeMM</span></a>
            @auth
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold px-3 py-1 rounded-pill bg-gradient bg-primary text-white shadow-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">@csrf <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
            </div>
            @endauth
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
    <script src="/jquery.min.js"></script>
    <script src="/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/service-worker.js');
        });
      }
    </script>
</body>
</html>
