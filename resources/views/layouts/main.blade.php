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
        .pf-mobile-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            max-width: 100vw;
            z-index: 1040;
            display: none;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            border-top: 1px solid #d8e4ef;
            background: rgba(248, 252, 255, 0.98);
            backdrop-filter: blur(8px);
            box-shadow: 0 -8px 0px rgba(16, 67, 124, 0.12);
            padding-bottom: calc(0.25rem + env(safe-area-inset-bottom));
        }
        body.modal-open .pf-mobile-nav,
        body.offcanvas-open .pf-mobile-nav {
            display: none !important;
        }
        .pf-mobile-nav-item {
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            text-decoration: none;
            color: #5f7388;
            padding: 0.45rem 0.2rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .pf-mobile-nav-item i {
            font-size: 1.25rem;
            line-height: 1;
        }
        .pf-mobile-nav-item span {
            line-height: 1.1;
            white-space: nowrap;
        }
        .pf-mobile-nav-item.is-active {
            color: #1f73e0;
            font-weight: 700;
        }
        .pf-mobile-nav-item:active {
            background-color: rgba(31, 115, 224, 0.08);
        }

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
        .navbar.sticky-top .dropdown-menu {
            background: #ffffff;
            border: 1px solid rgba(31, 115, 224, 0.14) !important;
        }
        .navbar.sticky-top .dropdown-menu .dropdown-item,
        .navbar.sticky-top .dropdown-menu .dropdown-item-text {
            color: #1e3e5f !important;
        }
        .navbar.sticky-top .dropdown-menu .text-muted {
            color: #6b7f93 !important;
        }
        .navbar.sticky-top .dropdown-menu .bi {
            color: currentColor !important;
        }
        .navbar.sticky-top .dropdown-menu .dropdown-item.text-danger,
        .navbar.sticky-top .dropdown-menu .dropdown-item.text-danger .bi {
            color: #dc3545 !important;
        }
        .navbar.sticky-top .dropdown-menu .dropdown-item:hover,
        .navbar.sticky-top .dropdown-menu .dropdown-item:focus {
            background-color: #eef5ff;
        }
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
        .form-control,
        .form-select,
        textarea.form-control {
            background-color: #ffffff !important;
            border-color: #c9d8e6;
            color: #25405c;
        }
        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            background-color: #ffffff !important;
            border-color: rgba(31, 115, 224, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(31, 115, 224, 0.16);
        }
        .form-control:disabled,
        .form-select:disabled,
        .form-control[readonly],
        .form-select[readonly] {
            background-color: #e9ecef !important;
            color: #6b7f93;
        }
        .pf-toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            pointer-events: none;
        }
        .pf-toast {
            pointer-events: auto;
            min-width: 280px;
            max-width: min(92vw, 420px);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 12px 30px rgba(15, 58, 104, 0.25);
            overflow: hidden;
            backdrop-filter: blur(6px);
            color: #ecf4ff;
        }
        .pf-toast .toast-body {
            white-space: pre-line;
            font-size: 0.94rem;
        }
        .pf-toast-success { background: linear-gradient(135deg, rgba(15, 153, 123, 0.98), rgba(22, 183, 151, 0.98)); }
        .pf-toast-error { background: linear-gradient(135deg, rgba(175, 48, 65, 0.98), rgba(218, 78, 94, 0.98)); }
        .pf-toast-warning { background: linear-gradient(135deg, rgba(171, 121, 29, 0.98), rgba(212, 152, 36, 0.98)); }
        .pf-toast-info { background: linear-gradient(135deg, rgba(31, 98, 180, 0.98), rgba(40, 141, 219, 0.98)); }
        @media (max-width: 991.98px) {
            body {
                padding-top: 66px;
                padding-bottom: calc(68px + env(safe-area-inset-bottom));
                -webkit-overflow-scrolling: touch;
            }
            .page-mobile-safe { padding-bottom: 5.4rem; }
            .sidebar-desktop { display: none !important; }
            .pf-mobile-nav { display: grid !important; }
            .navbar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                z-index: 1045 !important;
            }
            .main-wrapper-d-flex {
                display: block !important;
            }
            .main-content {
                display: block !important;
                min-height: auto;
            }
        }
        @media (min-width: 992px) {
            .sidebar-desktop { display: block !important; }
            .pf-mobile-nav { display: none !important; }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="main-wrapper-d-flex d-flex">
        <aside class="sidebar-desktop bg-white border-end p-3 shadow-sm">
            @include('layouts.partials.sidebar')
        </aside>

        <div class="main-content d-flex flex-column w-100">
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
    <div id="pf-toast-container" class="pf-toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            if (typeof window.bootstrap !== 'undefined') return;

            function findOffcanvasTarget(trigger) {
                const selector = trigger.getAttribute('data-bs-target');
                if (!selector) return null;
                return document.querySelector(selector);
            }

            function showOffcanvas(el) {
                if (!el) return;
                el.classList.add('show');
                el.style.visibility = 'visible';
                el.style.transform = 'none';
                document.body.classList.add('offcanvas-open');
            }

            function hideOffcanvas(el) {
                if (!el) return;
                el.classList.remove('show');
                el.style.visibility = '';
                el.style.transform = '';
                document.body.classList.remove('offcanvas-open');
            }

            document.addEventListener('click', function (event) {
                const toggle = event.target.closest('[data-bs-toggle="offcanvas"]');
                if (toggle) {
                    event.preventDefault();
                    const target = findOffcanvasTarget(toggle);
                    if (target && target.classList.contains('show')) {
                        hideOffcanvas(target);
                    } else {
                        showOffcanvas(target);
                    }
                    return;
                }

                const dismissOffcanvas = event.target.closest('[data-bs-dismiss="offcanvas"]');
                if (dismissOffcanvas) {
                    event.preventDefault();
                    const offcanvas = dismissOffcanvas.closest('.offcanvas');
                    hideOffcanvas(offcanvas);
                    return;
                }

                const dismissModal = event.target.closest('[data-bs-dismiss="modal"]');
                if (dismissModal) {
                    event.preventDefault();
                    const modal = dismissModal.closest('.modal');
                    if (modal) {
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                        modal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop.pf-fallback');
                        if (backdrop) backdrop.remove();
                        modal.dispatchEvent(new Event('hidden.bs.modal'));
                    }
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                const shownOffcanvas = document.querySelector('.offcanvas.show');
                if (shownOffcanvas) hideOffcanvas(shownOffcanvas);
                const shownModal = document.querySelector('.modal.show');
                if (shownModal) {
                    shownModal.classList.remove('show');
                    shownModal.style.display = 'none';
                    shownModal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop.pf-fallback');
                    if (backdrop) backdrop.remove();
                    shownModal.dispatchEvent(new Event('hidden.bs.modal'));
                }
            });
        })();

        (function () {
            const container = document.getElementById('pf-toast-container');
            if (!container) return;

            function iconByType(type) {
                if (type === 'success') return 'bi-check-circle-fill';
                if (type === 'error') return 'bi-exclamation-octagon-fill';
                if (type === 'warning') return 'bi-exclamation-triangle-fill';
                return 'bi-info-circle-fill';
            }

            function titleByType(type) {
                if (type === 'success') return 'สำเร็จ';
                if (type === 'error') return 'ผิดพลาด';
                if (type === 'warning') return 'แจ้งเตือน';
                return 'ข้อมูล';
            }

            function fallbackShow(toastEl, delay) {
                toastEl.style.opacity = '0';
                toastEl.style.transform = 'translateY(-8px)';
                toastEl.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                requestAnimationFrame(function () {
                    toastEl.style.opacity = '1';
                    toastEl.style.transform = 'translateY(0)';
                });

                setTimeout(function () {
                    toastEl.style.opacity = '0';
                    toastEl.style.transform = 'translateY(-8px)';
                    setTimeout(function () {
                        toastEl.remove();
                    }, 220);
                }, delay);
            }

            window.PFPopup = {
                show: function (message, type, options) {
                    const resolvedType = type || 'info';
                    const resolvedOptions = options || {};
                    const title = resolvedOptions.title || titleByType(resolvedType);
                    const delay = Number(resolvedOptions.delay || 2800);

                    const toastEl = document.createElement('div');
                    toastEl.className = 'toast pf-toast pf-toast-' + resolvedType;
                    toastEl.setAttribute('role', 'status');
                    toastEl.setAttribute('aria-live', 'polite');
                    toastEl.setAttribute('aria-atomic', 'true');
                    toastEl.innerHTML = '' +
                        '<div class="toast-header text-white border-0 bg-transparent">' +
                        '  <i class="bi ' + iconByType(resolvedType) + ' me-2"></i>' +
                        '  <strong class="me-auto">' + title + '</strong>' +
                        '  <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '</div>' +
                        '<div class="toast-body"></div>';

                    const bodyEl = toastEl.querySelector('.toast-body');
                    if (bodyEl) bodyEl.textContent = String(message || '');
                    container.appendChild(toastEl);

                    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                        const toast = new bootstrap.Toast(toastEl, {
                            autohide: true,
                            delay: delay,
                        });
                        toastEl.addEventListener('hidden.bs.toast', function () {
                            toastEl.remove();
                        });
                        toast.show();
                    } else {
                        fallbackShow(toastEl, delay);
                    }
                },
                success: function (message, options) { this.show(message, 'success', options); },
                error: function (message, options) { this.show(message, 'error', options); },
                warning: function (message, options) { this.show(message, 'warning', options); },
                info: function (message, options) { this.show(message, 'info', options); },
            };

            window.pfNotify = function (message, type, options) {
                const resolver = window.PFPopup;
                if (!resolver) return;
                resolver.show(message, type, options);
            };
        })();
    </script>
    @stack('scripts')
</body>
</html>
