<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PlayFlow Spa POS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('img/icon.png') }}">
    <style>
        :root {
            --bs-primary: #1f73e0;
            --bs-primary-rgb: 31, 115, 224;
            --bs-primary-text-emphasis: #154f9b;
            --bs-primary-bg-subtle: #dfeafe;
            --bs-primary-border-subtle: #b4cef9;

            --bs-info: #2d8ff0;
            --bs-info-rgb: 45, 143, 240;
            --bs-info-text-emphasis: #1b63ad;
            --bs-info-bg-subtle: #e5f1ff;
            --bs-info-border-subtle: #b7d9ff;

            --bs-success: #14b89a;
            --bs-success-rgb: 20, 184, 154;
            --bs-success-text-emphasis: #0f8a74;
            --bs-success-bg-subtle: #dcf7f2;
            --bs-success-border-subtle: #a5eadc;

            --bs-link-color: #1f73e0;
            --bs-link-hover-color: #165bb3;
            --bs-body-bg: #eef8fb;
            --bs-body-color: #25405c;
        }
        body { font-family: 'Prompt', sans-serif; background: radial-gradient(circle at top right, #d4edf9 0%, #e4f1f8 45%, #edf5fa 100%); color: #25405c; }
        .sidebar-desktop { width: 280px; height: 100vh; position: sticky; top: 0; z-index: 1000; overflow-y: auto; }
        .main-content { flex: 1; min-height: 100vh; background: transparent; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .nav-link { border-radius: 0.5rem; margin-bottom: 0.2rem; transition: all 0.2s; }
        .nav-link:hover { background-color: rgba(31, 115, 224, 0.1); }
        .nav-link.active { background: linear-gradient(135deg, #2d8ff0, #14b89a) !important; color: white !important; }
        .bg-primary { background: linear-gradient(135deg, #2d8ff0, #14b89a) !important; }
        .btn-primary { background: linear-gradient(135deg, #2d8ff0, #14b89a); border-color: #1f73e0; }
        .btn-primary:hover { background: linear-gradient(135deg, #246fd0, #109079); border-color: #246fd0; }
        .table-light { --bs-table-bg: #edf5ff; --bs-table-color: #265e9a; }
        .sidebar-desktop.bg-white {
            background: linear-gradient(180deg, #edf4fa 0%, #e8f1f8 58%, #e9f2f9 100%) !important;
            border-right-color: rgba(31, 115, 224, 0.14) !important;
        }
        .sidebar-desktop.shadow-sm { box-shadow: 2px 0 14px rgba(18, 76, 148, 0.08) !important; }
        .sidebar-desktop .link-dark { color: #2e3f55 !important; }
        .sidebar-desktop .link-dark:hover { background-color: rgba(31, 115, 224, 0.08); }
        .sidebar-desktop .text-muted { color: #5c728a !important; }
        .fixed-bottom.bg-white { background-color: #f8fbfe !important; }
        .navbar.sticky-top {
            background: linear-gradient(120deg, #1f73e0, #14b89a) !important;
            border-bottom-color: rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 6px 20px rgba(22, 95, 178, 0.2);
        }
        .navbar.sticky-top h5,
        .navbar.sticky-top small,
        .navbar.sticky-top .text-dark,
        .navbar.sticky-top .text-muted,
        .navbar.sticky-top .link-dark,
        .navbar.sticky-top .dropdown-toggle,
        .navbar.sticky-top .bi { color: #ffffff !important; }
        .navbar.sticky-top .dropdown-toggle {
            background-color: rgba(255, 255, 255, 0.16) !important;
            border: 1px solid rgba(255, 255, 255, 0.26);
        }
        .navbar.sticky-top .dropdown-toggle:hover { background-color: rgba(255, 255, 255, 0.24) !important; }
        .navbar.sticky-top img.border-primary { border-color: rgba(255, 255, 255, 0.82) !important; }
        .form-control:focus, .form-select:focus { border-color: rgba(31, 115, 224, 0.5); box-shadow: 0 0 0 0.25rem rgba(31, 115, 224, 0.16); }
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
