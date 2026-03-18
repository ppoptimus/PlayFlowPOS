<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button
                class="btn mobile-menu-btn d-lg-none me-2"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar"
                aria-controls="mobileSidebar"
                aria-label="เปิดเมนูหลัก"
            >
                <span class="mobile-menu-btn-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <div>
                <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'PlayFlow System')</h5>
                <small class="text-muted">@yield('page_subtitle', 'Branch')</small>
            </div>
        </div>

        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a
                    href="#"
                    class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle bg-light p-2 rounded-pill px-3"
                    id="userDropdown"
                    data-bs-toggle="dropdown"
                >
                    <img
                        src="https://i.pravatar.cc/100?u={{ urlencode((string) (optional(auth()->user())->username ?? 'user')) }}"
                        alt="user"
                        width="32"
                        height="32"
                        class="rounded-circle me-2 border border-primary"
                    >
                    <span class="d-none d-md-inline small fw-bold">{{ optional(auth()->user())->name ?? 'User' }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><span class="dropdown-item-text text-muted small">{{ optional(auth()->user())->username ?? '-' }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
