<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PlayFlow Spa POS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f4f7f6; color: #2c3e50; }
        .sidebar-desktop { width: 280px; height: 100vh; position: sticky; top: 0; z-index: 1000; overflow-y: auto; }
        .main-content { flex: 1; min-height: 100vh; background: #f4f7f6; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .nav-link { border-radius: 0.5rem; margin-bottom: 0.2rem; transition: all 0.2s; }
        .nav-link:hover { background-color: rgba(13, 110, 253, 0.05); }
        .nav-link.active { background-color: #0d6efd !important; color: white !important; }
        @media (max-width: 991.98px) { .sidebar-desktop { display: none; } }
    </style>
    @stack('head')
</head>
<body>
    <div class="d-flex">
        <aside class="sidebar-desktop bg-white border-end p-3 shadow-sm">
            @include('layouts.partials.sidebar')
        </aside>

        <div class="main-content d-flex flex-column">
            @include('layouts.partials.navbar')

            <main class="container-fluid p-4 mb-5">
                @yield('content')
            </main>
        </div>
    </div>

    @include('layouts.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>