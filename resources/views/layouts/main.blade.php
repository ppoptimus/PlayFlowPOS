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
        .navbar.sticky-top .mobile-menu-btn {
            background: linear-gradient(135deg, rgba(23, 120, 214, 0.95), rgba(22, 184, 156, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 8px 18px rgba(14, 82, 153, 0.28);
            color: #ffffff !important;
        }
        .navbar.sticky-top .mobile-menu-btn:hover,
        .navbar.sticky-top .mobile-menu-btn:focus-visible {
            background: linear-gradient(135deg, rgba(19, 102, 184, 1), rgba(18, 158, 134, 1));
            border-color: rgba(255, 255, 255, 0.65);
            color: #ffffff !important;
        }
        .mobile-sidebar-offcanvas .offcanvas-header {
            background: linear-gradient(120deg, #1f73e0, #14b89a);
            color: #ffffff;
        }
        .mobile-sidebar-offcanvas .offcanvas-title { color: #ffffff; }
        .mobile-sidebar-offcanvas .btn-close { filter: brightness(0) invert(1); }
        .mobile-sidebar-offcanvas {
            width: min(90vw, 360px);
        }
        .mobile-sidebar-offcanvas .offcanvas-body {
            background: linear-gradient(180deg, #f7fbff 0%, #eff8fc 52%, #eefaf6 100%);
        }
        .mobile-menu-heading {
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
            color: #3a6d9d;
        }
        .mobile-menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid rgba(29, 111, 178, 0.15);
            border-radius: 0.9rem;
            padding: 0.78rem 0.85rem;
            text-decoration: none;
            background-color: #ffffff;
            color: #1d4f7f;
            box-shadow: 0 6px 16px rgba(14, 68, 126, 0.06);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .mobile-menu-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(14, 68, 126, 0.1);
            border-color: rgba(22, 137, 137, 0.35);
            color: #1d4f7f;
        }
        .mobile-menu-link.active {
            background: linear-gradient(135deg, #1f73e0, #14b89a);
            color: #ffffff;
            border-color: rgba(16, 113, 150, 0.9);
        }
        .mobile-menu-icon {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.65rem;
            background: rgba(31, 115, 224, 0.12);
            color: #1b65bb;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .mobile-menu-link.active .mobile-menu-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }
        .mobile-menu-content {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .mobile-menu-title {
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.15;
            word-break: break-word;
        }
        .mobile-menu-subtitle {
            font-size: 0.74rem;
            opacity: 0.85;
            margin-top: 0.15rem;
            line-height: 1.2;
            word-break: break-word;
        }
        .mobile-menu-arrow {
            margin-left: auto;
            opacity: 0.72;
            flex-shrink: 0;
        }
        .mobile-menu-link.active .mobile-menu-arrow { opacity: 1; }
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

    <div class="offcanvas offcanvas-start mobile-sidebar-offcanvas d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold" id="mobileSidebarLabel">เมนูหลัก</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3">
            @include('layouts.partials.mobile-sidebar')
        </div>
    </div>

    @include('layouts.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
