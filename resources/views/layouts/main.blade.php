<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PlayFlow Spa POS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pf-blue: #2fb8ea;
            --pf-blue-deep: #168dcf;
            --pf-teal: #1cc9b6;
            --pf-mint: #7de4d0;
            --pf-navy: #224874;
            --pf-white: #ffffff;
            --pf-bg: linear-gradient(140deg, #ecf8ff 0%, #eafdf8 52%, #f7fbff 100%);
            --pf-card: rgba(255, 255, 255, 0.86);
            --pf-border: rgba(46, 174, 222, 0.22);
            --pf-shadow: 0 14px 40px rgba(34, 72, 116, 0.14);
            --pf-shadow-sm: 0 8px 24px rgba(45, 118, 176, 0.16);
            --pf-radius: 24px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Prompt', sans-serif;
            color: #245074;
            background: var(--pf-bg);
            position: relative;
            isolation: isolate;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            filter: blur(2px);
        }

        body::before {
            width: 360px;
            height: 360px;
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, rgba(47, 184, 234, 0.28) 0%, rgba(47, 184, 234, 0) 70%);
        }

        body::after {
            width: 420px;
            height: 420px;
            left: -140px;
            bottom: -200px;
            background: radial-gradient(circle, rgba(28, 201, 182, 0.24) 0%, rgba(28, 201, 182, 0) 70%);
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .pf-sidebar {
            width: 260px;
            padding: 28px 18px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(14px);
            border-right: 1px solid var(--pf-border);
            box-shadow: 10px 0 30px rgba(31, 102, 153, 0.08);
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .pf-logo {
            text-decoration: none;
            display: block;
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 22px;
            color: #fff;
            background: linear-gradient(125deg, var(--pf-blue-deep), var(--pf-teal));
            box-shadow: var(--pf-shadow-sm);
            font-weight: 700;
        }

        .pf-subtitle {
            margin: 0;
            font-size: 0.74rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .pf-nav {
            display: grid;
            gap: 8px;
        }

        .pf-nav-link {
            text-decoration: none;
            color: #356186;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .pf-nav-link:hover {
            border-color: var(--pf-border);
            background: rgba(46, 184, 234, 0.08);
        }

        .pf-nav-link.active {
            color: #0f6ea8;
            background: linear-gradient(120deg, rgba(47, 184, 234, 0.2), rgba(28, 201, 182, 0.18));
            border-color: rgba(48, 172, 220, 0.3);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.6);
        }

        .pf-main {
            flex: 1;
            padding: 26px;
            padding-bottom: 92px;
        }

        .pf-topbar {
            border: 1px solid var(--pf-border);
            border-radius: 20px;
            padding: 12px 16px;
            margin-bottom: 18px;
            background: linear-gradient(120deg, rgba(49, 184, 233, 0.16), rgba(28, 201, 182, 0.14));
            box-shadow: var(--pf-shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .pf-title {
            margin: 0;
            font-size: 1.3rem;
            line-height: 1.3;
            color: #1f4a76;
            font-weight: 700;
        }

        .pf-top-note {
            margin: 2px 0 0;
            color: #4f7fa6;
            font-size: 0.82rem;
        }

        .pf-user {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(44, 170, 212, 0.2);
            border-radius: 999px;
            padding: 5px 6px 5px 12px;
            white-space: nowrap;
        }

        .pf-user-name {
            font-size: 0.84rem;
            color: #22577f;
            font-weight: 600;
        }

        .pf-user img {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid rgba(47, 184, 234, 0.35);
        }

        .pf-card {
            background: var(--pf-card);
            border: 1px solid var(--pf-border);
            border-radius: var(--pf-radius);
            box-shadow: var(--pf-shadow);
            backdrop-filter: blur(9px);
            padding: 16px;
        }

        .pf-section-title {
            margin: 0;
            font-weight: 700;
            color: #20507b;
            font-size: 1.05rem;
        }

        .pf-badge {
            background: linear-gradient(110deg, #31b8e9, #1cc9b6);
            color: #fff;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pf-mobile-nav {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 10px;
            z-index: 20;
            border-radius: 18px;
            border: 1px solid rgba(45, 167, 215, 0.3);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 10px 28px rgba(35, 88, 132, 0.24);
            display: none;
            padding: 5px;
            gap: 4px;
        }

        .pf-mobile-link {
            flex: 1;
            border-radius: 12px;
            text-decoration: none;
            color: #2f6088;
            font-size: 0.74rem;
            font-weight: 600;
            text-align: center;
            padding: 8px 5px;
        }

        .pf-mobile-link.active {
            background: linear-gradient(120deg, rgba(49, 184, 233, 0.22), rgba(28, 201, 182, 0.24));
            color: #196da4;
        }

        @media (max-width: 991px) {
            .pf-sidebar { display: none; }
            .pf-main { padding: 14px 14px 92px; }
            .pf-topbar { padding: 10px 12px; border-radius: 16px; }
            .pf-title { font-size: 1.08rem; }
            .pf-user { padding: 3px 4px 3px 9px; }
            .pf-user-name { font-size: 0.72rem; }
            .pf-user img { width: 30px; height: 30px; }
            .pf-card { border-radius: 18px; padding: 12px; }
            .pf-mobile-nav { display: flex; }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="app-shell">
        <aside class="pf-sidebar">
            <a href="{{ route('dashboard') }}" class="pf-logo">
                PlayFlow POS
                <p class="pf-subtitle">Spa & Massage Management</p>
            </a>
            <nav class="pf-nav">
                <a href="{{ route('dashboard') }}" class="pf-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                <a href="{{ route('pos') }}" class="pf-nav-link {{ request()->routeIs('pos') ? 'active' : '' }}">🛍️ POS ขายบริการ</a>
                <a href="{{ route('booking') }}" class="pf-nav-link {{ request()->routeIs('booking') ? 'active' : '' }}">📅 จองคิว</a>
                <a href="{{ route('staff') }}" class="pf-nav-link {{ request()->routeIs('staff') ? 'active' : '' }}">👥 Staff</a>
            </nav>
        </aside>

        <main class="pf-main">
            <header class="pf-topbar">
                <div>
                    <h1 class="pf-title">@yield('page_title', 'PlayFlow Spa System')</h1>
                    <p class="pf-top-note">@yield('page_subtitle', 'Mockup mode (no MySQL)')</p>
                </div>
                <div class="pf-user">
                    <div class="pf-user-name">Manager @ Sukhumvit</div>
                    <img src="https://i.pravatar.cc/100?u=manager" alt="Manager">
                </div>
            </header>

            @yield('content')
        </main>
    </div>

    <nav class="pf-mobile-nav">
        <a href="{{ route('dashboard') }}" class="pf-mobile-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">📊<br>Dashboard</a>
        <a href="{{ route('pos') }}" class="pf-mobile-link {{ request()->routeIs('pos') ? 'active' : '' }}">🛍️<br>POS</a>
        <a href="{{ route('booking') }}" class="pf-mobile-link {{ request()->routeIs('booking') ? 'active' : '' }}">📅<br>Booking</a>
        <a href="{{ route('staff') }}" class="pf-mobile-link {{ request()->routeIs('staff') ? 'active' : '' }}">👥<br>Staff</a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
